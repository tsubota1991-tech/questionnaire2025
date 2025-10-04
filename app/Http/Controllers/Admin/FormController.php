<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * フォーム一覧
     */
    public function index(Request $request)
    {
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
     * - public_path をユニークなランダム文字列で採番
     * - public_path_generated_at を現在時刻でセット
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

        $form = DB::transaction(function () use ($validated) {
            // ランダム文字列（重複があれば作り直し）
            $slug = $this->makeUniquePublicPath(16);

            return Form::create([
                'owner_user_id'            => auth()->id(),
                'title'                    => $validated['title'],
                'description'              => $validated['description'] ?? null,
                'is_active'                => (bool)($validated['is_active'] ?? true),

                // ★ 追加
                'public_path'              => $slug,
                'public_path_generated_at' => now(),
            ]);
        });

        return redirect()->route('forms.index')
            ->with('status', "「{$form->title}」を作成しました。");
    }

    /**
     * 詳細
     */
public function show(Form $form)
{
    // 公開URL（public側ルート: public.forms.landing）
    $publicUrl = $form->public_path
        ? route('public.forms.landing', $form->public_path)
        : null;

    return view('forms.show', compact('form', 'publicUrl'));
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
     * - 既定では public_path は変更しない（再発行機能は別アクションで用意すると安全）
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
     * アーカイブ
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

    /**
     * ユニークな public_path を生成
     * - 既存の未削除レコードと重複しないことを保証
     */
    private function makeUniquePublicPath(int $length = 16): string
    {
        // まれな衝突に備えて最大数回リトライ（必要十分）
        for ($i = 0; $i < 5; $i++) {
            $slug = Str::random($length); // 英数字（URL安全）
            $exists = Form::query()
                ->where('public_path', $slug)
                ->whereNull('deleted_at') // ソフトデリートはユニーク対象外（再利用可）
                ->exists();

            if (!$exists) {
                return $slug;
            }
        }
        // 万一連続で衝突したら長さを伸ばして再帰
        return $this->makeUniquePublicPath($length + 1);
    }
}
