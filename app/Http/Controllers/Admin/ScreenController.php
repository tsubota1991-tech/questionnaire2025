<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 基底Controller
use App\Models\Form;
use App\Models\Screen;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ScreenController extends Controller
{
    /**
     * 画面一覧（フォーム配下）
     * Route: forms.screens.index
     */
    public function index(Form $form, Request $request)
    {
        $q = $request->string('q')->toString();

        $screens = Screen::query()
            ->where('form_id', $form->id)
            ->when($q, fn($query) =>
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%");
                })
            )
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('screens.index', compact('form', 'screens', 'q'));
    }

    /**
     * 作成フォーム
     * Route: forms.screens.create
     */
    public function create(Form $form)
    {
        return view('screens.create', compact('form'));
    }

    /**
     * 登録
     * Route: forms.screens.store
     */
    public function store(Form $form, Request $request)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'display_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ], [], [
            'title' => 'タイトル',
        ]);

        $screen = Screen::create([
            'form_id'            => $form->id,
            'created_by_user_id' => auth()->id(),
            'title'              => $validated['title'],
            'display_order'      => $validated['display_order'] ?? ($form->screens()->max('display_order') + 1),
            'is_active'          => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('forms.screens.index', $form)
            ->with('status', "画面「{$screen->title}」を作成しました。");
    }

    /**
     * 詳細（shallow）
     * Route: screens.show
     */
    public function show(Screen $screen)
    {
        $form = $screen->form;

        // 現在この画面に配置されている質問（display_order順）
        // ScreenQuestion 経由で question 情報を eager load
        $placed = $screen->screenQuestions()
            ->with(['question' => function ($q) {
                $q->select('id','form_id','type','title','is_required','is_active');
            }])
            ->orderBy('display_order')
            ->get();

        return view('screens.show', compact('screen', 'form', 'placed'));
    }

    /**
     * 編集（shallow）
     * Route: screens.edit
     */
    public function edit(Screen $screen)
    {
        $form = $screen->form;
        return view('screens.edit', compact('screen', 'form'));
    }

    /**
     * 更新（shallow）
     * Route: screens.update
     */
    public function update(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'display_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'      => ['nullable', 'boolean'],
        ], [], [
            'title' => 'タイトル',
        ]);

        $screen->update([
            'title'         => $validated['title'],
            'display_order' => $validated['display_order'] ?? $screen->display_order,
            'is_active'     => (bool)($validated['is_active'] ?? $screen->is_active),
        ]);

        return redirect()->route('screens.show', $screen)
            ->with('status', '画面を更新しました。');
    }

    /**
     * 削除（shallow）
     * Route: screens.destroy
     */
    public function destroy(Screen $screen)
    {
        $title = $screen->title;
        $form  = $screen->form; // 戻り先用
        $screen->delete();

        return redirect()->route('forms.screens.index', $form)
            ->with('status', "画面「{$title}」を削除しました。");
    }

    /**
     * 並び替え保存（shallow）
     * Route: screens.reorder (PATCH/PUT)
     * payload例: order[]=12&order[]=15&order[]=9 （画面内の質問順ではなく「画面のdisplay_order」を更新）
     */
    public function reorder(Request $request, Screen $screen)
    {
        $validated = $request->validate([
            'display_order' => ['required', 'integer', 'min:1'],
        ], [], ['display_order' => '表示順序']);

        DB::transaction(function () use ($screen, $validated) {
            // 1) まず対象の希望順へ一旦更新（ここで既存と衝突しうるため直接当てない）
            // → 代わりにあとでまとめて並べ替える
            // 対象の順序をそのまま用い、兄弟リスト内で並び直す
            $targetOrder = (int)$validated['display_order'];

            $siblings = Screen::where('form_id', $screen->form_id)
                ->orderBy('display_order')->orderBy('id')->get();

            // 対象を除外して希望位置へ差し込み
            $list = $siblings->reject(fn($s) => $s->id === $screen->id)->values();
            $insertAt = max(0, min($targetOrder - 1, $list->count()));
            $list->splice($insertAt, 0, [$screen]);

            // 2) 衝突回避のため一時的に大きい番号へ退避
            $base = 100000;
            foreach ($list as $i => $row) {
                $row->updateQuietly(['display_order' => $base + $i + 1]);
            }
            // 3) 最終的に 1..n で確定
            foreach ($list as $i => $row) {
                $row->updateQuietly(['display_order' => $i + 1]);
            }
        });

        return back()->with('status', '並び順を更新し、1番から連番に整えました。');
    }
}
