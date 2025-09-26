<?php

namespace App\Http\Controllers;

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
     * - order[question_id] = 並び順（数字） ※配置済みの順序編集
     * - add[]    = 追加する question_id 配列
     * - remove[] = 削除する question_id 配列
     */
    public function update(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'order'   => ['array'],
            'order.*' => ['numeric', 'min:0'],
            'add'     => ['array'],
            'add.*'   => ['integer'],
            'remove'  => ['array'],
            'remove.*'=> ['integer'],
        ]);

        $order  = collect($validated['order']  ?? []); // [qid => orderNum]
        $add    = collect($validated['add']    ?? [])->map(fn($v)=>(int)$v);
        $remove = collect($validated['remove'] ?? [])->map(fn($v)=>(int)$v);

        // 同一フォームに属さないIDははじく（安全対策）
        $formId = $screen->form_id;

        $validAddIds = Question::where('form_id', $formId)
            ->whereIn('id', $add)
            ->pluck('id');

        $existingPlaced = ScreenQuestion::where('screen_id', $screen->id)->pluck('question_id');

        // 1) 送られてきた既存配置の順序を「数値昇順」で並べ替え → その順で保持
        $sortedExistingIds = $order
            ->filter(fn($ord, $qid) => $existingPlaced->contains((int)$qid))  // 既存のみ
            ->sortBy(fn($ord) => (int)$ord)
            ->keys()
            ->map(fn($qid)=>(int)$qid)
            ->values();

        // 2) 追加分（既に含まれていないIDのみ）
        $appendIds = $validAddIds
            ->reject(fn($qid) => $sortedExistingIds->contains($qid))
            ->values();

        // 3) 削除対象を適用：最終リストから除外
        $finalIds = $sortedExistingIds
            ->reject(fn($qid) => $remove->contains($qid))
            ->concat($appendIds)
            ->unique()
            ->values();

        DB::transaction(function () use ($screen, $finalIds) {
            // 既存で final に含まれないものは削除
            ScreenQuestion::where('screen_id', $screen->id)
                ->whereNotIn('question_id', $finalIds->all())
                ->delete();

            // display_order を 1..n で再採番して upsert
            $order = 1;
            foreach ($finalIds as $qid) {
                ScreenQuestion::updateOrCreate(
                    ['screen_id' => $screen->id, 'question_id' => $qid],
                    ['display_order' => $order++]
                );
            }
        });

        return redirect()
            ->route('screens.show', $screen)
            ->with('status', '質問の配置を更新しました。');
    }
}
