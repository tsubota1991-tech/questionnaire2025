<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 基底Controller
use App\Models\Form;
use App\Models\Response;
use App\Models\ResponseItem;
use App\Models\Question;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseController extends Controller
{
    /**
     * 回答一覧（フォーム単位）
     */
    public function index(Form $form, Request $request)
    {
        $statuses = ['submitted','invalid','in_progress']; // マイグレーションに合わせて調整可
        $status = $request->string('status')->toString();

        $responses = Response::query()
            ->where('form_id', $form->id)
            ->when($status && in_array($status, $statuses), fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('responses.index', compact('form', 'responses', 'status', 'statuses'));
    }

    /**
     * 回答詳細（回答IDは ULID 前提）
     */
    public function show(Response $response)
    {
        $form = $response->form;

        // 回答詳細（関連アイテムを設問と選択肢も含めて）
        $items = ResponseItem::query()
            ->with(['question:id,title,type', 'option:id,label,value'])
            ->where('response_id', $response->id)
            ->orderBy('id')
            ->get();

        return view('responses.show', compact('form', 'response', 'items'));
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
     * 簡易集計（件数のみ）
     */
    public function analytics(Form $form)
    {
        $counts = Response::selectRaw("status, COUNT(*) as cnt")
            ->where('form_id', $form->id)
            ->groupBy('status')
            ->pluck('cnt','status');

        $total = $counts->sum();

        return view('responses.analytics', compact('form', 'counts', 'total'));
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
                // 回答アイテムを [question_id => 値] にマップ
                $items = ResponseItem::where('response_id', $res->id)->get();
                $map = [];
                foreach ($items as $it) {
                    // 選択式は label / value、自由記述は text、数値/日付はそれぞれの列を優先
                    $val = null;
                    if (!is_null($it->selected_option_id)) {
                        $val = optional($it->option)->label ?? $it->option_id; // label を優先
                    } elseif (!is_null($it->numeric_value)) {
                        $val = $it->numeric_value;
                    } elseif (!is_null($it->date_value)) {
                        $val = $it->date_value;
                    } else {
                        $val = $it->free_text;
                    }
                    $map[$it->question_id][] = $val; // 複数選択に対応
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
