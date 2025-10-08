<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\Question\StoreRequest;
use App\Http\Requests\Admin\Question\UpdateRequest;

class QuestionController extends Controller
{
    /**
     * 日本語/英語どちらで来ても英語コードへ正規化
     * 例) '単一選択' -> 'single_choice', 'single_choice' -> 'single_choice'
     */
    private static function normalizeType(?string $raw): ?string
    {
        if ($raw === null || $raw === '') return $raw;

        // すでに英語コードならそのまま
        if (array_key_exists($raw, Question::TYPE_MAP)) {
            return $raw;
        }

        // 日本語→英語（TYPE_MAP の value を逆引き）
        $code = array_search($raw, Question::TYPE_MAP, true);
        return $code !== false ? $code : $raw;
    }

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
        // 画面のプルダウン用にモデルの定数を利用
        $typeMap = Question::TYPE_MAP;

        // old 値が日本語でも英語でも現在値に対応（英語コードに寄せる）
        $rawType     = old('type', '');
        $currentType = self::normalizeType($rawType);

        // max_select と old('options') の大きい方を採用
        $oldOptionsArr = old('options', []);
        $n = max((int)old('max_select', 6), count($oldOptionsArr));

        // 空も含め、0..n-1 の添字で固定長配列を作る
        $initialOptions = [];
        for ($i = 0; $i < $n; $i++) {
            $initialOptions[$i] = (string) (old("options.$i.label") ?? '');
        }

        $initialMax = $n;

        return view('questions.create', compact(
            'form','typeMap','currentType','initialOptions','initialMax'
        ));
    }

    public function store(StoreRequest $request, Form $form)
    {
        $data = $request->validated();
        // ★ type を英語コードに正規化（日本語でPOSTされてもOK）
        $data['type'] = self::normalizeType($data['type'] ?? '');

        DB::transaction(function () use ($data, $form) {
            // 1) 質問レコード作成
            $question = Question::create([
                'form_id'       => $form->id,
                'type'          => $data['type'],
                'title'         => $data['title'],
                'help_text'     => $data['help_text'] ?? null,
                'is_required'   => (bool)($data['is_required'] ?? false),
                'max_select'    => (int)($data['max_select'] ?? 0),
                'display_order' => (int)($data['display_order'] ?? 0),
                'is_active'     => (bool)($data['is_active'] ?? true),
            ]);

            // 2) 選択肢（選択式の時のみ）
            if (in_array($question->type, ['single_choice','multi_choice'], true)) {
                $rows = $data['options'] ?? [];

                // 空行・重複を掃除しつつ並び順を付与
                $clean = [];
                foreach ($rows as $row) {
                    $label = isset($row['label']) ? trim((string)$row['label']) : '';
                    if ($label === '') continue;

                    $clean[] = [
                        'question_id'   => $question->id,
                        'label'         => $label,
                        'value'         => isset($row['value']) && $row['value'] !== ''
                                            ? (string)$row['value']
                                            : $label, // value未指定なら label を保存値に
                        'display_order' => count($clean) + 1, // 1,2,3...
                        'is_active'     => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }

                if (!empty($clean)) {
                    // question_id + display_order の UNIQUE を満たす並びで一括挿入
                    QuestionOption::insert($clean);
                }
            }
        });

        return redirect()
            ->route('forms.questions.index', ['form' => $form->id])
            ->with('success', '質問を作成しました。');
    }

    /**
     * 詳細（shallow）
     * Route: questions.show
     */
    public function show(Question $question)
    {
        $question->load(['form','options' => fn($q) => $q->orderBy('display_order')]);
        $form = $question->form;
        return view('questions.show', compact('question', 'form'));
    }

    /**
     * 編集（shallow）
     * Route: questions.edit
     */
    public function edit(Question $question)
    {
        $question->load(['form','options' => fn($q)=>$q->orderBy('display_order')]);
        $form = $question->form;

        $typeMap = Question::TYPE_MAP;

        $rawType     = old('type', $question->type ?? '');
        $currentType = self::normalizeType($rawType);

        // DB のラベル（display_order 順）
        $dbLabels = $question->options->pluck('label')->all();

        // max_select / DB件数 / old件数 の最大を採用
        $oldOptionsArr = old('options', []);
        $n = max(
            (int)old('max_select', $question->max_select),
            count($dbLabels),
            count($oldOptionsArr)
        );

        // 0..n-1 の添字で固定長。old があれば優先、無ければ DB、無ければ空文字
        $initialOptions = [];
        for ($i = 0; $i < $n; $i++) {
            $oldVal = old("options.$i.label");
            $initialOptions[$i] = $oldVal !== null ? (string)$oldVal : (string)($dbLabels[$i] ?? '');
        }

        $initialMax = $n;

        // エラーを JS に渡すための加工
        $optionErrorMap = [];
        foreach (session('errors')?->getMessages() ?? [] as $key => $msgs) {
            if (preg_match('/^options\.(\d+)\.label$/', $key, $m)) {
                $optionErrorMap[(int)$m[1]] = $msgs[0] ?? 'この項目は必須です。';
            }
        }
        $optionMinError = session('errors')?->first('options');

        return view('questions.edit', compact(
            'form','question','typeMap','currentType','initialOptions','initialMax','optionErrorMap','optionMinError'
        ));
    }

    /**
     * 更新（shallow）
     * Route: questions.update
     */
    public function update(UpdateRequest $request, Form $form, Question $question)
    {
        $data = $request->validated();
        // ★ type を英語コードに正規化
        $data['type'] = self::normalizeType($data['type'] ?? '');

        DB::transaction(function () use ($data, $question) {
            // 1) 質問本体を更新
            $question->fill([
                'type'          => $data['type'],
                'title'         => $data['title'],
                'help_text'     => $data['help_text'] ?? null,
                'is_required'   => (bool)($data['is_required'] ?? false),
                'max_select'    => (int)($data['max_select'] ?? 0),
                'display_order' => (int)($data['display_order'] ?? 0),
                'is_active'     => (bool)($data['is_active'] ?? true),
            ])->save();

            // 2) 選択肢（選択式のみ）
            if (in_array($question->type, ['single_choice','multi_choice'], true)) {
                $rows = $data['options'] ?? [];

                // クリーンアップ＆display_order 付与
                $clean = [];
                foreach ($rows as $row) {
                    $label = isset($row['label']) ? trim((string)$row['label']) : '';
                    if ($label === '') continue;
                    $clean[] = [
                        'label'         => $label,
                        'value'         => ($row['value'] ?? '') !== '' ? (string)$row['value'] : $label,
                    ];
                }

                // 既存を取得（並び順で）
                $existing = $question->options()->orderBy('display_order')->get();
                $targetCount = count($clean);
                $existCount  = $existing->count();
                $max = max($targetCount, $existCount);

                for ($i = 0; $i < $max; $i++) {
                    $displayOrder = $i + 1;

                    if ($i < $targetCount) {
                        // 目標がある
                        $payload = [
                            'label'         => $clean[$i]['label'],
                            'value'         => $clean[$i]['value'],
                            'display_order' => $displayOrder,
                            'is_active'     => true,
                        ];

                        if ($i < $existCount) {
                            // 既存を上書き
                            $existing[$i]->fill($payload);
                            if (method_exists($existing[$i], 'restore')) $existing[$i]->restore();
                            $existing[$i]->save();
                        } else {
                            // 追加作成
                            $question->options()->create($payload);
                        }
                    } else {
                        // 余剰は削除（ソフトデリート）
                        if ($i < $existCount) {
                            $existing[$i]->delete();
                        }
                    }
                }
            } else {
                // 非選択式なら選択肢は全て無効化（必要なら削除でもOK）
                $question->options()->delete();
            }
        });

        return redirect()
            ->route('forms.questions.index', ['form' => $question->form_id])
            ->with('success', '質問を更新しました。');
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
            'display_order' => ['required', 'integer', 'min:1'],
        ], [], [
            'display_order' => '表示順序',
        ]);

        DB::transaction(function () use ($question, $validated) {
            $targetOrder = (int)$validated['display_order'];

            // 同じフォーム内の兄弟を現在の順で取得
            $siblings = Question::where('form_id', $question->form_id)
                ->orderBy('display_order')
                ->orderBy('id') // 安定ソート
                ->get();

            // 対象を除いた配列を作り、希望位置(= targetOrder-1)へ差し込み
            $list = $siblings->reject(fn($q) => $q->id === $question->id)->values();
            $insertAt = max(0, min($targetOrder - 1, $list->count()));
            $list->splice($insertAt, 0, [$question]);

            // 衝突回避のため一時的に大きい番号へ退避
            $base = 100000;
            foreach ($list as $i => $row) {
                $row->updateQuietly(['display_order' => $base + $i + 1]);
            }

            // 最終的に 1..n を確定
            foreach ($list as $i => $row) {
                $row->updateQuietly(['display_order' => $i + 1]);
            }
        });

        return back()->with('status', '質問の表示順を更新し、1番から連番に整えました。');
    }
}
