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

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2"><span class="text-muted">種別：</span> <strong>{{ $question->type }}</strong></div>
      <div class="mb-2"><span class="text-muted">質問文：</span> <strong>{{ $question->title }}</strong></div>
      @if($question->help_text)
        <div class="mb-2"><span class="text-muted">補足：</span> {!! nl2br(e($question->help_text)) !!}</div>
      @endif
      <div class="mb-2">
        <span class="text-muted">必須：</span>
        @if($question->is_required) <span class="badge bg-danger-subtle border text-danger">必須</span>
        @else <span class="badge bg-secondary">任意</span> @endif
      </div>
      <div class="mb-2"><span class="text-muted">選択肢最大数：</span> {{ $question->max_select }}</div>
      <div class="mb-2"><span class="text-muted">表示順序：</span> {{ $question->display_order }}</div>
      <div class="text-muted small">作成日：{{ $question->created_at }} / 更新日：{{ $question->updated_at }}</div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="{{ route('questions.edit', $question) }}">編集</a>

    <form method="POST" action="{{ route('questions.reorder', $question) }}" class="d-inline-flex align-items-center gap-2">
      @csrf @method('PATCH')
      <label class="small text-muted">順序</label>
      <input type="number" name="display_order" class="form-control form-control-sm" style="width:120px" value="{{ $question->display_order }}" min="0">
      <button class="btn btn-outline-info btn-sm" type="submit">更新</button>
    </form>

    <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('削除しますか？');">
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
