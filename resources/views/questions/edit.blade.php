@extends('layouts.admin')
@section('title','質問編集')
@section('content')

@php
  // options.X.label に付いたエラーを {X: "msg"} へ
  $optionErrorMap = [];
  foreach ($errors->getMessages() as $key => $msgs) {
      if (preg_match('/^options\.(\d+)\.label$/', $key, $m)) {
          $optionErrorMap[(int)$m[1]] = $msgs[0] ?? 'この項目は必須です。';
      }
  }
  // 配列全体エラー（件数不足など）
  $optionMinError = $errors->first('options');
@endphp

{{-- JSへ初期値とエラーを渡す（※外部JSより前に） --}}
<script>
  // DB/old() 由来の初期値
  window.initialOldOptions = @json($initialOptions);  // 例: ["とてもよい","よい",...]
  window.initialOldType    = @json($currentType);     // 例: "multi_choice"
  window.initialOldMax     = {{ (int)$initialMax }};  // 例: 5

  // バリデーションエラー
  window.optionErrors      = @json($optionErrorMap);  // {0:"必須", 2:"..."} のような形
  window.optionMinError    = @json($optionMinError);  // "選択肢は :min 件以上..." など

  console.log('[edit.blade] initialOldOptions =', window.initialOldOptions);
  console.log('[edit.blade] initialOldType    =', window.initialOldType);
  console.log('[edit.blade] initialOldMax     =', window.initialOldMax);
</script>
<script src="{{ mix('js/admin/questions-create.js') }}" defer></script>

<h1 class="h4 mb-3">
  質問編集（フォーム #{{ $form->id }}：{{ $form->title }} / 質問 #{{ $question->id }}）
</h1>

<div class="mb-3">
  <a class="btn btn-secondary btn-sm" href="{{ route('forms.questions.index', ['form' => $form]) }}">← 質問一覧へ戻る</a>
</div>

<form method="POST" action="{{ route('questions.update', $question) }}">
  @csrf
  @method('PUT')

  {{-- 種別 --}}
  <div class="mb-3">
    <label class="form-label">種別 <span class="text-danger">*</span></label>
    <select id="q_type" name="type" class="form-select @error('type') is-invalid @enderror" required>
      <option value="">選択してください</option>
      @foreach ($typeMap as $code => $label)
        <option value="{{ $code }}" @selected(($currentType ?? $question->type) === $code)>{{ $label }}</option>
      @endforeach
    </select>
    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- 質問文 --}}
  <div class="mb-3">
    <label class="form-label">質問文 <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $question->title) }}" maxlength="300" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- 補足説明 --}}
  <div class="mb-3">
    <label class="form-label">補足説明</label>
    <textarea name="help_text" class="form-control @error('help_text') is-invalid @enderror" rows="3">{{ old('help_text', $question->help_text) }}</textarea>
    @error('help_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">選択肢最大数</label>
      <input type="number" id="max_select" name="max_select" class="form-control @error('max_select') is-invalid @enderror"
             value="{{ old('max_select', max($question->max_select, $question->options->count())) }}" min="0" max="50">
      @error('max_select') <div class="invalid-feedback">{{ $message }}</div> @enderror
      <div class="form-text">単一/複数選択のときに利用します。</div>
    </div>
    <div class="col-md-4">
      <label class="form-label">表示順序</label>
      <input type="number" name="display_order" class="form-control @error('display_order') is-invalid @enderror"
             value="{{ old('display_order', $question->display_order) }}" min="0" placeholder="未指定なら末尾に配置">
      @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4 d-flex align-items-center">
      <div class="form-check mt-4">
        <input type="hidden" name="is_required" value="0">
        <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1"
               {{ old('is_required', $question->is_required) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_required">必須</label>
      </div>
    </div>
  </div>

  <div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
           {{ old('is_active', $question->is_active) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">有効にする</label>
  </div>

  {{-- 選択肢入力欄は questions-create.js が生成（errorsも反映） --}}

  <div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">更新する</button>
    <a class="btn btn-secondary" href="{{ route('forms.questions.index', ['form' => $form]) }}">一覧へ戻る</a>
  </div>
</form>
@endsection
