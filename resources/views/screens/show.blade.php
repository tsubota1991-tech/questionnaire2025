@extends('layouts.admin')
@section('title','画面詳細')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">画面詳細 #{{ $screen->id }}</h1>
    <div class="ms-auto">
      @if($screen->is_active)
        <span class="badge bg-success">有効</span>
      @else
        <span class="badge bg-secondary">無効</span>
      @endif
    </div>
  </div>

  <div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.screens.index', $form) }}">← 画面一覧へ戻る</a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2"><span class="text-muted">タイトル：</span> <strong>{{ $screen->title }}</strong></div>
      <div class="mb-2"><span class="text-muted">表示順序：</span> {{ $screen->display_order }}</div>
      <div class="text-muted small">作成日：{{ $screen->created_at }} / 更新日：{{ $screen->updated_at }}</div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="{{ route('screens.edit', $screen) }}">編集</a>
    <a class="btn btn-outline-dark" href="{{ route('screen_questions.edit', $screen) }}">質問配置</a>
    <form method="POST" action="{{ route('screens.reorder', $screen) }}" class="d-inline-flex align-items-center gap-2">
      @csrf @method('PATCH')
      <label class="small text-muted">順序</label>
      <input type="number" name="display_order" class="form-control form-control-sm" style="width:120px" value="{{ $screen->display_order }}" min="0">
      <button class="btn btn-outline-info btn-sm" type="submit">更新</button>
    </form>
    <form method="POST" action="{{ route('screens.destroy', $screen) }}" onsubmit="return confirm('削除しますか？');">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger" type="submit">削除</button>
    </form>
  </div>
@endsection
