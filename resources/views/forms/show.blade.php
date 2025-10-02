@extends('layouts.admin')
@section('title','フォーム詳細')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">フォーム詳細 #{{ $form->id }}</h1>
    <div class="ms-auto">
      @if($form->is_active)
        <span class="badge bg-success">有効</span>
      @else
        <span class="badge bg-secondary">無効</span>
      @endif
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-2"><span class="text-muted">タイトル：</span> <strong>{{ $form->title }}</strong></div>
      <div class="mb-2"><span class="text-muted">説明：</span> {!! nl2br(e($form->description)) !!}</div>
      <div class="text-muted small">作成日：{{ $form->created_at }} / 更新日：{{ $form->updated_at }}</div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-outline-secondary" href="{{ route('forms.edit', $form) }}">編集</a>
    <a class="btn btn-outline-info" href="{{ route('forms.preview', $form) }}">プレビュー</a>

    <form method="POST" action="{{ route('forms.destroy', $form) }}" onsubmit="return confirm('削除しますか？');">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger" type="submit">削除</button>
    </form>
  </div>

  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-dark" href="{{ route('forms.screens.index', $form) }}">画面一覧</a>
    {{-- 質問一覧・回答一覧への直リンは非表示にして導線を画面一覧に一本化 --}}
    <a class="btn btn-secondary" href="{{ route('forms.index') }}">フォーム一覧へ</a>
  </div>
@endsection
