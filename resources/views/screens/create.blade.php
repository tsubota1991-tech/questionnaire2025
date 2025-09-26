@extends('layouts.admin')
@section('title','画面作成')

@section('content')
  <h1 class="h4 mb-3">画面作成（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>

  <div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.screens.index', $form) }}">← 画面一覧へ戻る</a>
  </div>

  <form method="POST" action="{{ route('forms.screens.store', $form) }}" class="needs-validation" novalidate>
    @csrf

    <div class="mb-3">
      <label class="form-label">タイトル <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title') }}" maxlength="200" required>
      @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">表示順序</label>
      <input type="number" name="display_order" class="form-control @error('display_order') is-invalid @enderror"
             value="{{ old('display_order') }}" min="0" placeholder="未指定なら末尾に配置">
      @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
      <label class="form-check-label" for="is_active">有効にする</label>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">保存</button>
      <a class="btn btn-secondary" href="{{ route('forms.screens.index', $form) }}">一覧へ戻る</a>
    </div>
  </form>
@endsection
