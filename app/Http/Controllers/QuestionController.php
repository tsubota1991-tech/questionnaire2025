<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * 質問一覧（フォーム配下）
     * Route: forms.questions.index
     */
    public function index(Form $form, Request $request)
    {
        $q = $request->string('q')->toString();

        $questions = Question::query()
            ->where('form_id', $form->id)
            ->when($q, fn($query) =>
                $query->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('help_text', 'like', "%{$q}%");
                })
            )
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('questions.index', compact('form', 'questions', 'q'));
    }

    /**
     * 作成フォーム
     * Route: forms.questions.create
     */
    public function create(Form $form)
    {
        // enum候補（マイグレーションのtypeと一致させる）
        $types = ['single_choice', 'multi_choice', 'free_text', 'number', 'date'];
        return view('questions.create', compact('form', 'types'));
    }

    /**
     * 登録
     * Route: forms.questions.store
     */
    public function store(Form $form, Request $request)
    {
        $types = ['single_choice', 'multi_choice', 'free_text', 'number', 'date'];

        $validated = $request->validate([
            'type'          => ['required', 'in:'.implode(',', $types)],
            'title'         => ['required', 'string', 'max:300'],
            'help_text'     => ['nullable', 'string'],
            'is_required'   => ['nullable', 'boolean'],
            'max_select'    => ['nullable', 'integer', 'min:0', 'max:6'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ], [], [
            'type'  => '種別',
            'title' => '質問文',
        ]);

        $question = Question::create([
            'form_id'       => $form->id,
            'type'          => $validated['type'],
            'title'         => $validated['title'],
            'help_text'     => $validated['help_text'] ?? null,
            'is_required'   => (bool)($validated['is_required'] ?? false),
            'max_select'    => $validated['max_select'] ?? 6,
            'display_order' => $validated['display_order'] ?? ($form->questions()->max('display_order') + 1),
            'is_active'     => (bool)($validated['is_active'] ?? true),
        ]);

        return redirect()->route('forms.questions.index', $form)
            ->with('status', "質問「{$question->title}」を作成しました。");
    }

    /**
     * 詳細（shallow）
     * Route: questions.show
     */
    public function show(Question $question)
    {
        $form = $question->form; // 戻りリンク用
        return view('questions.show', compact('question', 'form'));
    }

    /**
     * 編集（shallow）
     * Route: questions.edit
     */
    public function edit(Question $question)
    {
        $form = $question->form;
        $types = ['single_choice', 'multi_choice', 'free_text', 'number', 'date'];
        return view('questions.edit', compact('question', 'form', 'types'));
    }

    /**
     * 更新（shallow）
     * Route: questions.update
     */
    public function update(Request $request, Question $question)
    {
        $types = ['single_choice', 'multi_choice', 'free_text', 'number', 'date'];

        $validated = $request->validate([
            'type'          => ['required', 'in:'.implode(',', $types)],
            'title'         => ['required', 'string', 'max:300'],
            'help_text'     => ['nullable', 'string'],
            'is_required'   => ['nullable', 'boolean'],
            'max_select'    => ['nullable', 'integer', 'min:0', 'max:6'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['nullable', 'boolean'],
        ], [], [
            'type'  => '種別',
            'title' => '質問文',
        ]);

        $question->update([
            'type'          => $validated['type'],
            'title'         => $validated['title'],
            'help_text'     => $validated['help_text'] ?? null,
            'is_required'   => (bool)($validated['is_required'] ?? $question->is_required),
            'max_select'    => $validated['max_select'] ?? $question->max_select,
            'display_order' => $validated['display_order'] ?? $question->display_order,
            'is_active'     => (bool)($validated['is_active'] ?? $question->is_active),
        ]);

        return redirect()->route('questions.show', $question)
            ->with('status', '質問を更新しました。');
    }

    /**
     * 削除（shallow）
     * Route: questions.destroy
     */
    public function destroy(Question $question)
    {
        $form = $question->form;
        $title = $question->title;
        $question->delete();

        return redirect()->route('forms.questions.index', $form)
            ->with('status', "質問「{$title}」を削除しました。");
    }

    /**
     * 並び順だけを更新（shallow）
     * Route: questions.reorder (PATCH/PUT)
     */
    public function reorder(Request $request, Question $question)
    {
        $validated = $request->validate([
            'display_order' => ['required', 'integer', 'min:0'],
        ]);

        $question->update(['display_order' => $validated['display_order']]);

        return back()->with('status', "質問の表示順を {$validated['display_order']} に更新しました。");
    }
}
