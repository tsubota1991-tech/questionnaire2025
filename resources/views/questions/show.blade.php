@extends('layouts.admin')
@section('title','質問詳細')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">質問詳細 #{{ $question->id }}</h1>
    <div class="ms-auto">
      @if($question->is_active)
        <span class="badge bg-success">有効</span>
      @else
        <span class="badge bg-secondary">無効</span>
      @endif
    </div>
  </div>

  <div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.questions.index', $form) }}">← 質問一覧へ戻る</a>
  </div>

  @php
    $isChoice = in_array($question->type, ['single_choice','multi_choice'], true);
  @endphp

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2">
        <span class="text-muted">種別：</span>
        <strong>{{ $question->type_label }}</strong>
      </div>

      <div class="mb-2">
        <span class="text-muted">質問文：</span>
        <strong>{{ $question->title }}</strong>
      </div>

      @if($question->help_text)
        <div class="mb-2">
          <span class="text-muted">補足：</span>
          {!! nl2br(e($question->help_text)) !!}
        </div>
      @endif

      <div class="mb-2">
        <span class="text-muted">必須：</span>
        @if($question->is_required)
          <span class="badge bg-danger-subtle border text-danger">必須</span>
        @else
          <span class="badge bg-secondary">任意</span>
        @endif
      </div>

      @if($isChoice && (int)$question->max_select > 0)
        <div class="mb-2">
          <span class="text-muted">選択可能上限：</span>
          {{ $question->max_select }}
        </div>
      @endif

      <div class="mb-2">
        <span class="text-muted">表示順序：</span>
        {{ $question->display_order }}
      </div>

      <div class="text-muted small">
        作成日：{{ $question->created_at }} / 更新日：{{ $question->updated_at }}
      </div>
    </div>
  </div>

  {{-- 選択式なら選択肢一覧を表示 --}}
  @if($isChoice)
    <div class="card mb-3">
      <div class="card-header py-2">
        <strong>選択肢</strong>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0 align-middle">
            <thead>
              <tr class="text-muted">
                <th style="width:90px;">順序</th>
                <th>ラベル</th>
                <th>保存値</th>
                <th style="width:110px;">状態</th>
              </tr>
            </thead>
            <tbody>
              @php
                // is_active カラムがある前提。なければ true 固定でもOK
                $opts = ($question->relationLoaded('options') ? $question->options : $question->options())
                          ->orderBy('display_order')->get();
              @endphp

              @forelse($opts as $opt)
                <tr>
                  <td class="text-muted">{{ $opt->display_order }}</td>
                  <td class="fw-semibold">{{ $opt->label }}</td>
                  <td class="text-monospace">{{ $opt->value }}</td>
                  <td>
                    @if($opt->is_active ?? true)
                      <span class="badge bg-success">有効</span>
                    @else
                      <span class="badge bg-secondary">無効</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-muted text-center py-3">選択肢は登録されていません。</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif

  <div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="{{ route('questions.edit', $question) }}">編集</a>

    <form method="POST" action="{{ route('questions.reorder', $question) }}" class="d-inline-flex align-items-center gap-2">
      @csrf @method('PATCH')
      <label class="small text-muted mb-0">順序</label>
      {{-- バリデーション(min:1)に合わせて min を 1 に修正 --}}
      <input type="number" name="display_order" class="form-control form-control-sm" style="width:120px"
             value="{{ $question->display_order }}" min="1">
      <button class="btn btn-outline-info btn-sm" type="submit">更新</button>
    </form>

    <form method="POST" action="{{ route('questions.destroy', $question) }}"
          onsubmit="return confirm('削除しますか？');">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger" type="submit">削除</button>
    </form>
  </div>

  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-dark" href="{{ route('forms.screens.index', $form) }}">画面一覧</a>
    <a class="btn btn-dark" href="{{ route('responses.index', $form) }}">回答一覧</a>
    <a class="btn btn-secondary" href="{{ route('forms.show', $form) }}">フォーム詳細へ</a>
  </div>
@endsection