@extends('layouts.admin')
@section('title','フォーム作成')

@section('content')
  <h1 class="h4 mb-3">フォーム作成</h1>

  <form method="POST" action="{{ route('forms.store') }}" class="needs-validation" novalidate>
    @csrf

    <div class="mb-3">
      <label class="form-label">タイトル <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title') }}" maxlength="200" required>
      @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">説明</label>
      <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                rows="4">{{ old('description') }}</textarea>
      @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
      <label class="form-check-label" for="is_active">有効にする</label>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">保存</button>
      <a class="btn btn-secondary" href="{{ route('forms.index') }}">一覧へ戻る</a>
    </div>
  </form>
@endsection
