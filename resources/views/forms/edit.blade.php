@extends('layouts.admin')
@section('title','フォーム編集')

@section('content')
  <h1 class="h4 mb-3">フォーム編集 #{{ $form->id }}</h1>

  <form method="POST" action="{{ route('forms.update', $form) }}" class="needs-validation" novalidate>
    @csrf @method('PUT')

    <div class="mb-3">
      <label class="form-label">タイトル <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title', $form->title) }}" maxlength="200" required>
      @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">説明</label>
      <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                rows="4">{{ old('description', $form->description) }}</textarea>
      @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
             {{ old('is_active', $form->is_active) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">有効にする</label>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">更新</button>
      <a class="btn btn-secondary" href="{{ route('forms.show', $form) }}">詳細へ戻る</a>
    </div>
  </form>
@endsection
