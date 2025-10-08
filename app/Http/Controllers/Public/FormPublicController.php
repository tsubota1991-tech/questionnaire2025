<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\QuestionOption;
use App\Models\Response;
use App\Models\ResponseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormPublicController extends Controller
{
/** フォーム取得（公開用） */
private function findPublicForm(string $slug): Form
{
    return Form::query()
        ->where('public_path', $slug)
        ->where('is_active', true)
        ->whereNull('deleted_at')
        ->firstOrFail();
}
private function pickUlid(Request $request): ?string
{
    $isUlid = fn($v) => is_string($v) && preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $v);
    foreach ([
        $request->query('r'),
        $request->input('r'),
        $request->session()->get('public_response_ulid'),
    ] as $v) {
        if ($isUlid($v)) return $v;
    }
    return null;
}
    /** スクリーン配列（is_active のみ, display_order 昇順） */
    private function activeScreens(Form $form)
    {
        return $form->screens()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    /** 入口 */
    public function landing(string $form_slug, Request $request)
    {
        $form = $this->findPublicForm($form_slug);
        $screens = $this->activeScreens($form);
        return view('public.forms.landing', compact('form', 'screens'));
    }

    /** スタート（RESPONSES 作成 → step 1へ） */
    public function start(string $form_slug, Request $request)
    {
        $form = $this->findPublicForm($form_slug);

        $resp = DB::transaction(function () use ($form, $request) {
            $response = new \App\Models\Response();
            $response->id         = (string) \Illuminate\Support\Str::ulid();
            $response->form_id    = $form->id;
            $response->status     = \App\Models\Response::STATUS_IN_PROGRESS;
            $response->client_ip  = $request->ip();
            $response->user_agent = substr($request->userAgent() ?? '', 0, 1024);
            $response->started_at = now();
            $response->save();
            return $response;
        });

        // ここでログ（ULID 長さも確認できると安心：26 が正しい）
        \Log::debug('[start] resp_id', ['id' => $resp->id, 'len' => strlen((string)$resp->id)]);

        // セッションにも保存（保険）
        $request->session()->put('public_response_ulid', $resp->id);

        // ?r= を必ず付けて遷移
        $url = route('public.forms.screen', [
            'form_slug' => $form_slug,
            'step'      => 1,
        ]) . '?r=' . urlencode($resp->id);

        return redirect()->to($url);
    }

    /** 指定 step の画面を描画 */
    public function screen(string $form_slug, int $step, Request $request)
    {
        \Log::debug('[screen] fullUrl', ['url' => $request->fullUrl(), 'r' => $request->query('r')]);

        $form    = $this->findPublicForm($form_slug);
        $screens = $this->activeScreens($form);
        abort_if($screens->isEmpty(), 404);

        $step   = max(1, min($step, $screens->count()));
        $screen = $screens[$step - 1];

        $r = $this->pickUlid($request);
        abort_if(empty($r), 403, '回答を開始していません。');
        $request->session()->put('public_response_ulid', $r);

        $response = Response::query()
            ->where('id', $r)->where('form_id', $form->id)
            ->firstOrFail();

        // ★ 中間の並び順 & 有効な質問のみ & 選択肢を eager load
        $questions = $screen->questions()
            ->where('questions.is_active', true)
            ->orderBy('screen_questions.display_order')
            ->with(['options' => fn($q)=>$q->where('is_active',true)->orderBy('display_order')])
            ->get();

        // 型の実際の値をログ（デバッグ用）
        \Log::debug('[screen] question types', $questions->map(fn($q)=>['id'=>$q->id,'type'=>$q->type])->all());

        $items = ResponseItem::query()
            ->where('response_id', $response->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->get();

        return view('public.forms.screen', compact(
            'form','screens','screen','step','response','questions','items'
        ));
    }

    /** step の送信（質問群を動的にバリデーション→回答保存→次へ） */
    public function submitScreen(string $form_slug, int $step, Request $request)
    {
        // フォーム & 画面
        $form    = $this->findPublicForm($form_slug);
        $screens = $this->activeScreens($form);
        abort_if($screens->isEmpty(), 404);

        $step   = max(1, min($step, $screens->count()));
        $screen = $screens[$step - 1];

        // ULID を取得（クエリ/POST/セッション）→ セッションへも保存
        $r = $this->pickUlid($request);
        abort_if(empty($r), 403, '回答を開始していません。');
        $request->session()->put('public_response_ulid', $r);

        // 回答本体
        /** @var \App\Models\Response $response */
        $response = Response::query()
            ->where('id', $r)->where('form_id', $form->id)
            ->firstOrFail();

        // 対象質問（pivot 並び & is_active）
        $questions = $screen->questions()
            ->where('questions.is_active', true)
            ->orderBy('screen_questions.display_order')
            ->get();

        // 動的バリデーション
        $rules = [];
        $attrs = [];
        foreach ($questions as $q) {
            $name        = "q.{$q->id}";
            $isRequired  = $q->is_required ? 'required' : 'nullable';
            $attrs[$name] = $q->title;

            switch ($q->type) {
                case 'single_choice':
                    $rules[$name] = [$isRequired, 'integer'];
                    break;
                case 'multi_choice':
                    $rules[$name] = [$isRequired, 'array'];
                    $rules["{$name}.*"] = ['integer', 'distinct'];
                    break;
                case 'free_text':
                    // response_items.free_text が 255 なのでここで制限
                    $rules[$name] = [$isRequired, 'string', 'max:255'];
                    break;
                case 'number':
                    $rules[$name] = [$isRequired, 'numeric'];
                    break;
                case 'date':
                    $rules[$name] = [$isRequired, 'date'];
                    break;
            }
        }

        $validated = $request->validate($rules, [], $attrs);

        // ★ validated はネスト：['q' => [ question_id => 値 ]]
        $payload = $validated['q'] ?? [];

        // 選択肢の存在チェック用（質問ごとに許容される option_id をマップ化）
        $optionMapByQuestion = QuestionOption::query()
            ->whereIn('question_id', $questions->pluck('id'))
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->groupBy('question_id')
            ->map(fn($col) => $col->pluck('id')->flip()); // id => idx （issetチェック用）

        // 保存（既存回答はクリア→再作成）
        DB::transaction(function () use ($response, $questions, $payload, $optionMapByQuestion) {
            foreach ($questions as $q) {
                // 既存クリア
                ResponseItem::query()
                    ->where('response_id', $response->id)
                    ->where('question_id', $q->id)
                    ->delete();

                // 未入力ならスキップ（任意項目など）
                if (!array_key_exists($q->id, $payload)) {
                    continue;
                }

                $val = $payload[$q->id];

                if ($q->type === 'single_choice') {
                    $optId = $val ? (int)$val : null;
                    $set   = $optionMapByQuestion[$q->id] ?? collect();
                    $exists = $set instanceof \Illuminate\Support\Collection ? $set->has($optId) : isset($set[$optId]);

                    if ($optId && $exists) {
                        ResponseItem::create([
                            'response_id' => $response->id,
                            'question_id' => $q->id,
                            'option_id'   => $optId,
                        ]);
                    }

                } elseif ($q->type === 'multi_choice') {
                    $ids = is_array($val) ? $val : [];
                    $set = $optionMapByQuestion[$q->id] ?? collect();

                    foreach ($ids as $optId) {
                        $optId = (int)$optId;
                        $exists = $set instanceof \Illuminate\Support\Collection ? $set->has($optId) : isset($set[$optId]);
                        if ($exists) {
                            ResponseItem::create([
                                'response_id' => $response->id,
                                'question_id' => $q->id,
                                'option_id'   => $optId,
                            ]);
                        }
                    }

                } elseif ($q->type === 'free_text') {
                    $text = is_string($val) ? mb_substr($val, 0, 255) : null;
                    if ($text !== null && $text !== '') {
                        ResponseItem::create([
                            'response_id' => $response->id,
                            'question_id' => $q->id,
                            'free_text'   => $text,
                        ]);
                    }

                } elseif ($q->type === 'number') {
                    if ($val !== null && $val !== '') {
                        ResponseItem::create([
                            'response_id'   => $response->id,
                            'question_id'   => $q->id,
                            'numeric_value' => $val,
                        ]);
                    }

                } elseif ($q->type === 'date') {
                    if ($val !== null && $val !== '') {
                        ResponseItem::create([
                            'response_id' => $response->id,
                            'question_id' => $q->id,
                            'date_value'  => $val,
                        ]);
                    }
                }
            }
        });

        // 確認ログ（任意で残してOK）
        $all = ResponseItem::query()->where('response_id', $response->id)->get();
        \Log::debug('[submitScreen] saved items', [
            'response_id' => $response->id,
            'count'       => $all->count(),
            'sample'      => $all->take(10)->map(fn($i)=>[
                'q' => $i->question_id,
                'opt' => $i->option_id,
                'text'=> $i->free_text,
                'num' => $i->numeric_value,
                'date'=> $i->date_value,
            ])->all(),
        ]);

        // 次のステップ or 確認へ（r を必ず付ける：route()に与えれば ?r= として付与されます）
        if ($step < $screens->count()) {
            return redirect()->route('public.forms.screen', [
                'form_slug' => $form_slug,
                'step'      => $step + 1,
                'r'         => $response->id,
            ]);
        }

        return redirect()->route('public.forms.confirm', [
            'form_slug' => $form_slug,
            'r'         => $response->id,
        ]);
    }


    /** 確認画面 */
    public function confirm(string $form_slug, Request $request)
    {
        // 1) フォームを取得（ここで $form を必ず作る）
        $form = $this->findPublicForm($form_slug);

        // 2) ULID を取得（クエリ/POST/セッションから正規表現で検証）
        $r = $this->pickUlid($request);
        abort_if(empty($r), 403, '回答を開始していません。');
        $request->session()->put('public_response_ulid', $r);

        // 3) 回答本体
        $response = \App\Models\Response::query()
            ->where('id', $r)->where('form_id', $form->id)
            ->firstOrFail();

        // 4) 画面・設問・選択肢（pivot順 & is_active）を eager load
        $screens = $this->activeScreens($form)->load(['questions' => function ($q) {
            $q->where('questions.is_active', true)
            ->orderBy('screen_questions.display_order')
            ->with(['options' => fn($o) => $o->where('is_active', true)->orderBy('display_order')]);
        }]);

        // 5) 回答明細を設問IDでグルーピング
        $items = \App\Models\ResponseItem::query()
            ->where('response_id', $response->id)
            ->get()
            ->groupBy('question_id');


        // ★ デバッグログ
        \Log::debug('[confirm] context', [
            'form_id'     => $form->id,
            'response_id' => $response->id,
            'screens'     => $screens->count(),
            'q_ids'       => $screens->flatMap->questions->pluck('id')->values()->all(),
            'items_count' => $items->flatten(1)->count(),
            'item_keys'   => $items->keys()->all(), // question_id のキー
            'item_sample' => $items->flatten(1)->take(10)->map(fn($i)=>[
                'q' => $i->question_id,
                'opt' => $i->option_id,
                'text'=> $i->free_text,
                'num' => $i->numeric_value,
                'date'=> $i->date_value,
            ])->all(),
        ]);
        
        // 6) ビューへ明示的に渡す（$form を含めるのがポイント）
        return view('public.forms.confirm', [
            'form'     => $form,
            'response' => $response,
            'screens'  => $screens,
            'items'    => $items,
        ]);
    }

    /** 送信（確定） */
    public function submit(string $form_slug, Request $request)
    {
        $form = $this->findPublicForm($form_slug);
        $r = $this->pickUlid($request);
        abort_if(empty($r), 403);
        $request->session()->put('public_response_ulid', $r);

        $response = Response::query()
            ->where('id', $r)->where('form_id', $form->id)
            ->firstOrFail();

        // ★ ここは submitted（モデル定数）
        $response->update([
            'status'       => Response::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return redirect()->route('public.forms.complete', ['form_slug' => $form_slug]);
    }

    /** 完了 */
    public function complete(string $form_slug, Request $request)
    {
        $form = $this->findPublicForm($form_slug);
        return view('public.forms.complete', compact('form'));
    }
}
