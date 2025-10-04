{{-- resources/views/forms/show.blade.php --}}
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

  {{-- ★ 追加：公開URL --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-start align-items-md-center gap-3 flex-column flex-md-row">
        <div class="flex-grow-1">
          <div class="text-muted small mb-1">公開URL</div>

          @if ($publicUrl)
            <div class="d-flex align-items-center flex-wrap gap-2">
              <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="link-primary">{{ $publicUrl }}</a>
              @unless($form->is_active)
                <span class="badge bg-secondary">現在は無効</span>
              @endunless
            </div>
            <div class="text-muted small mt-1">
              このリンクをエンドユーザーへ案内してください。
            </div>
          @else
            <div class="text-warning">公開URLが未発行です。</div>
            <div class="text-muted small">新規作成時／移行マイグレーションで自動発行されます。</div>
          @endif
        </div>

        @if ($publicUrl)
          <div class="d-flex gap-2 ms-md-auto">
            <button type="button" class="btn btn-outline-primary btn-sm" data-copy="{{ $publicUrl }}">URLをコピー</button>
            <a class="btn btn-primary btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">新しいタブで開く</a>
          </div>
        @endif
      </div>
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
    <a class="btn btn-secondary" href="{{ route('forms.index') }}">フォーム一覧へ</a>
  </div>
<script src="{{ mix('js/admin/form-show.js') }}" defer></script>
@endsection
