@extends('layouts.public')
@section('title', $form->title.' - 確認')

@section('content')
@php
  $r = request('r') ?: session('public_response_ulid');
@endphp

<style>
  html { scroll-behavior: smooth; }
  .scroll-section { scroll-margin-top: 90px; } /* アンカー位置のズレ対策 */
  .sticky-sidebar { position: sticky; top: 1rem; }
  .answer-block .q-title { font-weight: 600; }
  .answer-block .q-answer { color: #6c757d; }
</style>

<div class="row g-4">
  {{-- メイン（全セクションを展開表示） --}}
  <div class="col-12 col-lg-9 col-xl-9">
    <div class="card shadow-sm">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 mb-4">{{ $form->title }} - 入力内容の確認</h1>

        @foreach ($screens as $idx => $scr)
          @php
            $step = $idx + 1;

            // この画面の回答済み数を集計（バッジ用）
            $answered = 0;
            $totalQs  = $scr->questions->count();
            foreach ($scr->questions as $q) {
              $list = $items->get($q->id) ?? collect();
              $has = false;
              switch ($q->type) {
                case 'single_choice':
                  $has = (bool) optional($list->first())->option_id;
                  break;
                case 'multi_choice':
                  $has = $list->pluck('option_id')->filter()->isNotEmpty();
                  break;
                case 'free_text':
                  $has = filled(optional($list->first())->free_text);
                  break;
                case 'number':
                  $has = !is_null(optional($list->first())->numeric_value);
                  break;
                case 'date':
                  $has = filled(optional($list->first())->date_value);
                  break;
              }
              if ($has) $answered++;
            }
          @endphp

          <section id="scr-{{ $step }}" class="scroll-section mb-5">
            <div class="d-flex align-items-center mb-2">
              <h2 class="h5 mb-0">Step {{ $step }}：{{ $scr->title }}</h2>
              <span class="badge bg-secondary ms-2">{{ $answered }}/{{ $totalQs }}</span>
              <a class="btn btn-sm btn-outline-primary ms-auto"
                 href="{{ route('public.forms.screen', [$form->public_path, $step]) }}?r={{ $r }}">
                この画面を修正
              </a>
            </div>

            <div class="list-group answer-block">
              @foreach ($scr->questions as $q)
                @php
                  $list = $items->get($q->id) ?? collect();
                @endphp
                <div class="list-group-item py-3">
                  <div class="q-title">{{ $q->title }} @if($q->is_required)<span class="text-danger">*</span>@endif</div>
                  @if($q->help_text)
                    <div class="small text-muted mb-1">{{ $q->help_text }}</div>
                  @endif

                  <div class="q-answer">
                    @switch($q->type)
                      @case('single_choice')
                        @php
                          $optId  = optional($list->first())->option_id;
                          $label  = optional($q->options->firstWhere('id', $optId))->label;
                        @endphp
                        {{ $label ?? '（未回答）' }}
                        @break

                      @case('multi_choice')
                        @php
                          $ids    = $list->pluck('option_id')->all();
                          $labels = $q->options->whereIn('id', $ids)->pluck('label')->all();
                        @endphp
                        {{ $labels ? implode('、', $labels) : '（未回答）' }}
                        @break

                      @case('free_text')
                        {{ optional($list->first())->free_text ?? '（未回答）' }}
                        @break

                      @case('number')
                        {{ optional($list->first())->numeric_value ?? '（未回答）' }}
                        @break

                      @case('date')
                        @php
                          // ★ 時刻を表示しない：Y-m-d に整形
                          $d = optional($list->first())->date_value; // Carbon|string|null
                          if ($d instanceof \Carbon\CarbonInterface) {
                            $d = $d->format('Y-m-d');
                          } elseif (is_string($d)) {
                            // 先頭が YYYY-MM-DD ならその部分だけを使用
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $d, $m)) {
                              $d = substr($d, 0, 10);
                            }
                          }
                        @endphp
                        {{ $d ?? '（未回答）' }}
                        @break

                      @default
                        <span class="text-danger">未対応タイプ：{{ $q->type }}</span>
                    @endswitch
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        @endforeach

        <form method="POST" action="{{ route('public.forms.submit', $form->public_path) }}?r={{ $r }}"
              class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
          @csrf
          <a class="btn btn-outline-secondary"
             href="{{ route('public.forms.screen', [$form->public_path, 1]) }}?r={{ $r }}">
            修正する（Step 1へ）
          </a>
          <button class="btn btn-primary px-4" type="submit">送信する</button>
        </form>
      </div>
    </div>
  </div>

  {{-- 右カラム：インデックス（クリックで該当セクションへ） --}}
  <div class="col-12 col-lg-3 col-xl-3">
    <div class="card sticky-sidebar">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="fw-semibold">画面インデックス</div>
        </div>
        <div class="list-group">
          @foreach ($screens as $idx => $scr)
            @php
              $step = $idx + 1;

              // バッジ用の集計（軽量化のため再計算）
              $answered = 0;
              $totalQs  = $scr->questions->count();
              foreach ($scr->questions as $q) {
                $list = $items->get($q->id) ?? collect();
                $has = false;
                switch ($q->type) {
                  case 'single_choice':
                    $has = (bool) optional($list->first())->option_id; break;
                  case 'multi_choice':
                    $has = $list->pluck('option_id')->filter()->isNotEmpty(); break;
                  case 'free_text':
                    $has = filled(optional($list->first())->free_text); break;
                  case 'number':
                    $has = !is_null(optional($list->first())->numeric_value); break;
                  case 'date':
                    $has = filled(optional($list->first())->date_value); break;
                }
                if ($has) $answered++;
              }
            @endphp

            <a href="#scr-{{ $step }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
              <span class="text-truncate">Step {{ $step }}：{{ $scr->title }}</span>
              <span class="badge bg-secondary ms-2">{{ $answered }}/{{ $totalQs }}</span>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
