<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Screen;
use Illuminate\Http\Request;

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
        // 親フォームも渡すとビューで戻りリンクが作りやすい
        $form = $screen->form;
        return view('screens.show', compact('screen', 'form'));
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
        // ここでは単一画面の display_order を直接更新する簡易版
        $validated = $request->validate([
            'display_order' => ['required', 'integer', 'min:0'],
        ]);

        $screen->update(['display_order' => $validated['display_order']]);

        return back()->with('status', "画面の表示順を {$validated['display_order']} に更新しました。");
    }
}
