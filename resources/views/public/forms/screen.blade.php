@extends('layouts.public')
@section('title', $form->title.' - Step '.$step)
@section('content')
@php
  $total    = $screens->count();
  $progress = (int) round(($step / max(1,$total)) * 100);
  // クエリの r（ULID）が来ていればそれを、無ければセッションを利用
  $r = request()->query('r') ?: session('public_response_ulid');
@endphp

  <div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">

      {{-- タイトル + 進捗バー --}}
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-end">
          <h1 class="h4 mb-1">{{ $form->title }}</h1>
          <div class="text-muted small">Step {{ $step }} / {{ $total }}</div>
        </div>
        <div class="progress" role="progressbar" aria-label="進捗" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar" style="width: {{ $progress }}%">{{ $progress }}%</div>
        </div>
      </div>

      {{-- 画面カード --}}
      <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
          <h2 class="h5 mb-4">{{ $screen->title }}</h2>

          <form method="POST" action="{{ route('public.forms.screen.submit', [$form->public_path, $step]) }}?r={{ $r }}">
            @csrf
            <input type="hidden" name="r" value="{{ $r }}">

            @foreach ($questions as $q)
              @php
                // 型を正規化（日本語→英語コード）
                $map = ['単一選択'=>'single_choice','複数選択'=>'multi_choice','自由入力'=>'free_text','数値入力'=>'number','日付入力'=>'date'];
                $t   = $map[trim((string)$q->type)] ?? trim((string)$q->type);

                $name    = "q.{$q->id}";
                $oldVal  = old("q.$q->id");
                $myItems = $items->where('question_id', $q->id);

                // コントローラで eager load 済み想定
                $opts = $q->options ?? collect();
              @endphp

              <div class="mb-4">
                <label class="form-label fw-semibold">
                  {{ $q->title }}
                  @if($q->is_required) <span class="text-danger">*</span> @endif
                </label>
                @if($q->help_text)
                  <div class="text-muted small mb-2">{{ $q->help_text }}</div>
                @endif

                {{-- 種別ごとに描画 --}}
                @if ($t === 'single_choice')
                  @php $selected = $oldVal ?? optional($myItems->first())->option_id; @endphp
                  <div class="row row-cols-1 row-cols-md-2 g-2">
                    @forelse ($opts as $opt)
                      <div class="col">
                        <div class="form-check">
                          <input class="form-check-input @error($name) is-invalid @enderror"
                                 type="radio"
                                 name="q[{{ $q->id }}]"
                                 id="q{{ $q->id }}_{{ $opt->id }}"
                                 value="{{ $opt->id }}"
                                 @checked($selected == $opt->id)
                                 @if($q->is_required) aria-required="true" @endif
                          >
                          <label class="form-check-label" for="q{{ $q->id }}_{{ $opt->id }}">{{ $opt->label }}</label>
                        </div>
                      </div>
                    @empty
                      <div class="text-muted small">（選択肢が未登録/非表示です）</div>
                    @endforelse
                  </div>
                  @error($name) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                @elseif ($t === 'multi_choice')
                  @php $selected = is_array($oldVal) ? $oldVal : $myItems->pluck('option_id')->all(); @endphp
                  <div class="row row-cols-1 row-cols-md-2 g-2">
                    @forelse ($opts as $opt)
                      <div class="col">
                        <div class="form-check">
                          <input class="form-check-input @error($name) is-invalid @enderror"
                                 type="checkbox"
                                 name="q[{{ $q->id }}][]"
                                 id="q{{ $q->id }}_{{ $opt->id }}"
                                 value="{{ $opt->id }}"
                                 @checked(in_array($opt->id, $selected, true) || in_array((string)$opt->id, array_map('strval', $selected), true))
                                 @if($q->is_required) aria-required="true" @endif
                          >
                          <label class="form-check-label" for="q{{ $q->id }}_{{ $opt->id }}">{{ $opt->label }}</label>
                        </div>
                      </div>
                    @empty
                      <div class="text-muted small">（選択肢が未登録/非表示です）</div>
                    @endforelse
                  </div>
                  @error($name) <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                @elseif ($t === 'free_text')
                  @php $val = $oldVal ?? optional($myItems->first())->free_text; @endphp
                  <textarea name="q[{{ $q->id }}]" rows="4"
                            class="form-control @error($name) is-invalid @enderror"
                            @if($q->is_required) aria-required="true" @endif>{{ $val }}</textarea>
                  @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror

                @elseif ($t === 'number')
                  @php $val = $oldVal ?? optional($myItems->first())->numeric_value; @endphp
                  <input type="number" step="any" name="q[{{ $q->id }}]"
                         value="{{ $val }}"
                         class="form-control @error($name) is-invalid @enderror"
                         @if($q->is_required) aria-required="true" @endif>
                  @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror

                @elseif ($t === 'date')
                  @php
                    // ★ ここがポイント：Y-m-d へ整形してセット
                    $stored = optional($myItems->first())->date_value; // Carbon|string|null
                    $storedYmd = null;
                    if ($stored instanceof \Carbon\CarbonInterface) {
                      $storedYmd = $stored->format('Y-m-d');
                    } elseif (is_string($stored) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $stored)) {
                      $storedYmd = $stored; // 既に Y-m-d
                    }
                    $val = $oldVal ?? $storedYmd;
                  @endphp
                  <input type="date" name="q[{{ $q->id }}]"
                         value="{{ $val }}"
                         class="form-control @error($name) is-invalid @enderror"
                         @if($q->is_required) aria-required="true" @endif>
                  @error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror

                @else
                  <div class="text-danger small">未対応タイプ: {{ $q->type }}</div>
                @endif
              </div>
            @endforeach

            {{-- スマホで押しやすいボタン配置（PCでは右寄せ） --}}
            <div class="sticky-actions mt-3">
              <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                <div>
                  @if ($step > 1)
                    <a class="btn btn-outline-secondary"
                       href="{{ route('public.forms.screen', [$form->public_path, $step - 1]) }}?r={{ $r }}">← 戻る</a>
                  @else
                    <a class="btn btn-outline-secondary" href="{{ route('public.forms.landing', $form->public_path) }}">入口へ</a>
                  @endif
                </div>
                <button class="btn btn-primary px-4" type="submit">
                  {{ $step < $total ? '次へ' : '確認へ' }}
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
@endsection
