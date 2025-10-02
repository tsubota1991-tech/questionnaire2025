<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 基底Controller
use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    /**
     * フォーム一覧
     */
    public function index(Request $request)
    {
        // キーワード簡易検索（任意）
        $q = $request->string('q')->toString();
        $forms = Form::query()
            ->when($q, fn($query) =>
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                })
            )
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('forms.index', compact('forms', 'q'));
    }

    /**
     * 作成フォーム
     */
    public function create()
    {
        return view('forms.create');
    }

    /**
     * 登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ], [], [
            'title'       => 'タイトル',
            'description' => '説明',
        ]);

        $form = Form::create([
            'owner_user_id' => auth()->id(),
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'is_active'     => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('forms.index')
            ->with('status', "「{$form->title}」を作成しました。");
    }

    /**
     * 詳細
     */
    public function show(Form $form)
    {
        return view('forms.show', compact('form'));
    }

    /**
     * 編集フォーム
     */
    public function edit(Form $form)
    {
        return view('forms.edit', compact('form'));
    }

    /**
     * 更新
     */
    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ], [], [
            'title'       => 'タイトル',
            'description' => '説明',
        ]);

        $form->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active'   => (bool)($validated['is_active'] ?? $form->is_active),
        ]);

        return redirect()->route('forms.show', $form)
            ->with('status', 'フォームを更新しました。');
    }

    /**
     * 削除
     */
    public function destroy(Form $form)
    {
        $title = $form->title;
        $form->delete();

        return redirect()->route('forms.index')
            ->with('status', "「{$title}」を削除しました。");
    }

    /**
     * アーカイブ（= is_active を false に）
     */
    public function archive(Form $form)
    {
        $form->update(['is_active' => false]);
        return redirect()->route('forms.index')
            ->with('status', "「{$form->title}」をアーカイブしました。");
    }

    /**
     * プレビュー
     */
    public function preview(Form $form)
    {
        return view('forms.preview', compact('form'));
    }
}
