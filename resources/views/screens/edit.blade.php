@extends('layouts.admin')
@section('title','画面編集')

@section('content')
  <h1 class="h4 mb-3">画面編集 #{{ $screen->id }}</h1>

  <div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="{{ route('screens.show', $screen) }}">← 詳細へ戻る</a>
  </div>

  <form method="POST" action="{{ route('screens.update', $screen) }}" class="needs-validation" novalidate>
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">タイトル <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title', $screen->title) }}" maxlength="200" required>
      @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">表示順序</label>
      <input type="number" name="display_order" class="form-control @error('display_order') is-invalid @enderror"
             value="{{ old('display_order', $screen->display_order) }}" min="0">
      @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
             {{ old('is_active', $screen->is_active) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">有効にする</label>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">更新</button>
      <a class="btn btn-secondary" href="{{ route('screens.show', $screen) }}">詳細へ戻る</a>
    </div>
  </form>
@endsection
