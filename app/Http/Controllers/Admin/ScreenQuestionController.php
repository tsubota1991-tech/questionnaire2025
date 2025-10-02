<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 基底Controller
use App\Models\Screen;
use App\Models\Question;
use App\Models\ScreenQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScreenQuestionController extends Controller
{
    /**
     * 配置編集画面（画面に紐づく質問の確認・並び替え・追加/削除）
     */
    public function edit(Screen $screen)
    {
        $form = $screen->form;

        // 現在配置済み（display_order順）
        $placed = ScreenQuestion::query()
            ->with(['question' => function ($q) {
                $q->select('id', 'form_id', 'type', 'title', 'is_active');
            }])
            ->where('screen_id', $screen->id)
            ->orderBy('display_order')
            ->get();

        $placedQuestionIds = $placed->pluck('question_id')->all();

        // 未配置の質問（同一フォーム内）
        $available = Question::query()
            ->where('form_id', $form->id)
            ->whereNotIn('id', $placedQuestionIds)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get(['id','type','title','is_active']);

        return view('screens.layout', compact('screen', 'form', 'placed', 'available'));
    }

    /**
     * 保存：並び順の更新、追加、削除を一括反映
     *
     * 受信パラメータ:
     * - order[question_id] = 並び順（数字, 1以上） ※配置済みの順序編集
     * - add[]    = 追加する question_id 配列
     * - remove[] = 削除する question_id 配列
     */
    public function update(Request $request, Screen $screen)
    {
        // --- 入力の基本バリデーション ---
        $validated = $request->validate([
            'order'    => ['nullable', 'array'],
            'order.*'  => ['numeric', 'min:1'], // ★ 1 始まり
            'add'      => ['nullable', 'array'],
            'add.*'    => ['integer'],
            'remove'   => ['nullable', 'array'],
            'remove.*' => ['integer'],
        ], [], [
            'order'   => '並び順',
        ]);

        $order  = collect($validated['order']  ?? []); // [qid => orderNum]
        $add    = collect($validated['add']    ?? [])->map(fn($v)=>(int)$v);
        $remove = collect($validated['remove'] ?? [])->map(fn($v)=>(int)$v);

        $formId = $screen->form_id;

        // --- セキュリティ：同一フォーム以外のIDを排除 ---
        $validQuestionIds = Question::where('form_id', $formId)->pluck('id');
        $order = $order->filter(fn($ord, $qid) => $validQuestionIds->contains((int)$qid));
        $add   = $add->filter(fn($qid) => $validQuestionIds->contains((int)$qid));
        $remove= $remove->filter(fn($qid) => $validQuestionIds->contains((int)$qid));

        // --- 既に配置されている質問IDを取得 ---
        $existingPlaced = ScreenQuestion::where('screen_id', $screen->id)->pluck('question_id');

        // --- バリデーション補強：並び順の重複を弾く（同値があるときエラー） ---
        // order は「配置済みの順序編集」用なので、既存のみに対象を絞ってからチェック
        $ordersForExisting = $order
            ->filter(fn($ord, $qid) => $existingPlaced->contains((int)$qid))
            ->map(fn($ord) => (int)$ord);

        if ($ordersForExisting->count() !== $ordersForExisting->unique()->count()) {
            return back()
                ->withErrors(['order' => '表示順序に重複があります。'])
                ->withInput();
        }

        // --- 並べ替えアルゴリズム ---
        // 1) 既存分は「送信された order の昇順」で並べ直し
        $sortedExistingIds = $order
            ->filter(fn($ord, $qid) => $existingPlaced->contains((int)$qid))
            ->sortBy(fn($ord) => (int)$ord)
            ->keys()
            ->map(fn($qid) => (int)$qid)
            ->values();

        // 2) 追加分（まだ入っていないID）
        $appendIds = $add
            ->reject(fn($qid) => $sortedExistingIds->contains($qid))
            ->values();

        // 3) 削除適用：最終リストから remove を除外
        $finalIds = $sortedExistingIds
            ->reject(fn($qid) => $remove->contains($qid))
            ->concat($appendIds)
            ->unique()
            ->values(); // => 最終的にこの順で 1..n を振る

        // --- DB反映：トランザクション + 二段階更新でユニーク制約回避 ---
        DB::transaction(function () use ($screen, $finalIds) {
            // final に含まれない既存配置は削除
            ScreenQuestion::where('screen_id', $screen->id)
                ->whereNotIn('question_id', $finalIds->all())
                ->delete();

            // 衝突回避のため一時的に大きい番号へ退避
            $base = 100000;
            foreach ($finalIds as $i => $qid) {
                ScreenQuestion::updateOrCreate(
                    ['screen_id' => $screen->id, 'question_id' => $qid],
                    ['display_order' => $base + $i + 1] // 一時付与
                );
            }

            // 最後に 1..n で確定（ 1番からに統一）
            $order = 1;
            foreach ($finalIds as $qid) {
                ScreenQuestion::where('screen_id', $screen->id)
                    ->where('question_id', $qid)
                    ->update(['display_order' => $order++]);
            }
        });

        return redirect()
            ->route('screens.show', $screen)
            ->with('status', '質問の配置を更新しました。（1番から連番に整えました）');
    }
}
