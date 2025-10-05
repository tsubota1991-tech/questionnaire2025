<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 基底Controller
use App\Models\Form;
use App\Models\Response;
use App\Models\ResponseItem;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseController extends Controller
{
    /**
     * 回答一覧（フォーム単位）
     */
    public function index(Form $form, Request $request)
    {
        $statuses = ['submitted','invalid','in_progress'];
        $status = $request->string('status')->toString();

        // ★ ステータス日本語表記
        $statusJa = [
            'submitted'   => '提出済み',
            'invalid'     => '無効',
            'in_progress' => '進行中',
        ];

        $responses = Response::query()
            ->where('form_id', $form->id)
            ->when($status && in_array($status, $statuses, true), fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('responses.index', compact('form', 'responses', 'status', 'statuses', 'statusJa'));
    }

    /**
     * 古い回答の一括削除（ボタン実行）
     * 既定：2日前より古い invalid / in_progress を対象
     */
    public function purge(Form $form, Request $request)
    {
        // 入力（任意で上書き可能にしておく）
        $days     = (int) $request->input('days', 2);
        $statuses = $request->filled('statuses')
            ? collect(explode(',', (string) $request->input('statuses')))
                ->map(fn($s) => trim($s))->filter()->values()->all()
            : ['invalid', 'in_progress'];

        // 許容ステータスのバリデーション（保険）
        $allow = ['submitted','invalid','in_progress'];
        $statuses = array_values(array_intersect($statuses, $allow));
        if (empty($statuses)) {
            return back()->with('status', '削除対象ステータスの指定が不正です。');
        }

        $cutoff = now()->subDays($days);

        $base = Response::query()
            ->where('form_id', $form->id)
            ->whereIn('status', $statuses)
            ->where('created_at', '<', $cutoff);

        $count = (clone $base)->count();
        if ($count === 0) {
            return back()->with('status', "対象レコードはありません（{$days}日前より古い / ".implode(',', $statuses)."）。");
        }

        $deleted = 0;

        // ULID 主キーでも chunkById は利用可（念のためカラム名明示）
        $base->orderBy('id')->chunkById(500, function ($batch) use (&$deleted) {
            DB::transaction(function () use ($batch, &$deleted) {
                $ids = $batch->pluck('id');
                // responses 削除 → response_items は FK の cascadeOnDelete で連鎖削除
                Response::whereIn('id', $ids)->delete();
                $deleted += $ids->count();
            });
        }, 'id');

        return back()->with('status', "削除完了：{$deleted} 件（{$days}日前より古い / ".implode(',', $statuses)."）。");
    }

    /**
     * 回答詳細（回答IDは ULID 前提）
     */
    public function show(Response $response)
    {
        $form = $response->form;

        $items = ResponseItem::query()
            ->with(['question:id,title,type', 'option:id,label,value'])
            ->where('response_id', $response->id)
            ->orderBy('id')
            ->get();

        // 種別の日本語表記
        $typeJa = [
            'single_choice' => '単一選択',
            'multi_choice'  => '複数選択',
            'free_text'     => '自由入力',
            'number'        => '数値入力',
            'date'          => '日付入力',
        ];

        // ステータスの日本語表記
        $statusJa = [
            'submitted'   => '提出済み',
            'invalid'     => '無効',
            'in_progress' => '進行中',
        ];

        return view('responses.show', compact('form', 'response', 'items', 'typeJa', 'statusJa'));
    }

    /**
     * ステータス変更（submitted / invalid / in_progress）
     */
    public function changeStatus(Request $request, Response $response)
    {
        // テーブル定義に合わせて許容値を調整（あなたの enum が ['in_progress','submitted'] なら invalid は外してください）
        $allow = ['submitted','invalid','in_progress'];
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', $allow)],
        ], [], [
            'status' => 'ステータス'
        ]);

        $response->update(['status' => $validated['status']]);

        return back()->with('status', "回答のステータスを「{$validated['status']}」に変更しました。");
    }

    /**
     * CSVエクスポート（回答横持ち：設問ごとに1列）
     */
    public function export(Form $form): StreamedResponse
    {
        // 設問一覧（列見出し用）
        $questions = Question::where('form_id', $form->id)
            ->orderBy('display_order')->orderBy('id')
            ->get(['id','title','type']);

        $responses = Response::where('form_id', $form->id)
            ->orderBy('created_at')->get();

        $fileName = 'responses_form_'.$form->id.'_'.now()->format('Ymd_His').'.csv';

        $callback = function() use ($responses, $questions) {
            $out = fopen('php://output', 'w');

            // 文字化け回避：Excel向けにはBOM付与を好む環境もあります（必要ならコメントアウト解除）
            // fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // 見出し
            $headers = ['response_id','submitted_at','status'];
            foreach ($questions as $q) {
                $headers[] = "Q{$q->id}:{$q->title}";
            }
            fputcsv($out, $headers);

            // 回答行（各回答を横持ちで出力）
            foreach ($responses as $res) {
                $items = ResponseItem::where('response_id', $res->id)
                    ->with('option:id,label')
                    ->get();

                $map = [];
                foreach ($items as $it) {
                    if (!is_null($it->option_id)) { 
                        $val = optional($it->option)->label ?? $it->option_id;
                    } elseif (!is_null($it->numeric_value)) {
                        $val = $it->numeric_value;
                    } elseif (!is_null($it->date_value)) {
                        $val = $it->date_value;
                    } else {
                        $val = $it->free_text;
                    }
                    $map[$it->question_id][] = $val;       // 複数選択にも対応
                }

                $row = [$res->id, optional($res->created_at)->toDateTimeString(), $res->status];
                foreach ($questions as $q) {
                    $vals = $map[$q->id] ?? null;
                    $row[] = $vals ? implode(' / ', $vals) : '';
                }
                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
