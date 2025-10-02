@extends('layouts.admin')
@section('title','質問作成')
@section('content')

{{-- ★ JSに初期値を渡す（createでもeditでも共通でOK） --}}
<script>
  window.initialOldOptions = @json($initialOptions);       // 例: ["とてもよい","よい",...]
  window.initialOldType    = @json($currentType);          // 例: "multi_choice"
  window.initialOldMax     = {{ (int)$initialMax }};       // 例: 5

  console.log('[create.blade] initialOldOptions =', window.initialOldOptions);
  console.log('[create.blade] initialOldType    =', window.initialOldType);
  console.log('[create.blade] initialOldMax     =', window.initialOldMax);
</script>

{{-- ★ LaravelのバリデーションエラーをJSへ受け渡し --}}
@php
  $optionErrorMap = [];
  foreach ($errors->getMessages() as $key => $msgs) {
      if (preg_match('/^options\.(\d+)\.label$/', $key, $m)) {
          $optionErrorMap[(int)$m[1]] = $msgs[0] ?? 'この項目は必須です。';
      }
  }
  $optionMinError = $errors->first('options'); // ex) 「選択肢は :min 件以上…」
@endphp
<script>
  window.optionErrors   = @json($optionErrorMap);
  window.optionMinError = @json($optionMinError);
</script>

{{-- ★ 外部JSは一度だけ読み込む（重複読み込みはNG） --}}
<script src="{{ mix('js/admin/questions-create.js') }}" defer></script>

  <h1 class="h4 mb-3">質問作成（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>

  <div class="mb-3">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.questions.index', ['form' => $form]) }}">← 質問一覧へ戻る</a>
  </div>

  <form method="POST" action="{{ route('forms.questions.store', ['form' => $form]) }}" class="needs-validation" novalidate>
    @csrf

    {{-- 種別 --}}
    <div class="mb-3">
      <label class="form-label">種別 <span class="text-danger">*</span></label>
      <select id="q_type" name="type" class="form-select @error('type') is-invalid @enderror" required>
        <option value="">選択してください</option>
        @foreach ($typeMap as $code => $label)
          <option value="{{ $code }}" @selected($currentType === $code)>{{ $label }}</option>
        @endforeach
      </select>
      @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- 質問文 --}}
    <div class="mb-3">
      <label class="form-label">質問文 <span class="text-danger">*</span></label>
      <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title') }}" maxlength="300" required>
      @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- 補足説明 --}}
    <div class="mb-3">
      <label class="form-label">補足説明</label>
      <textarea name="help_text" class="form-control @error('help_text') is-invalid @enderror" rows="3">{{ old('help_text') }}</textarea>
      @error('help_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">選択肢最大数</label>
        <input
          type="number"
          id="max_select"
          name="max_select"
          class="form-control @error('max_select') is-invalid @enderror"
          value="{{ old('max_select', 6) }}"
          min="0"
          max="6"
        >
        @error('max_select') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">単一/複数選択のときに利用します。</div>
      </div>
      <div class="col-md-4">
        <label class="form-label">表示順序</label>
        <input type="number" name="display_order" class="form-control @error('display_order') is-invalid @enderror"
               value="{{ old('display_order') }}" min="0" placeholder="未指定なら末尾に配置">
        @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="col-md-4 d-flex align-items-center">
        <div class="form-check mt-4">
          <input type="hidden" name="is_required" value="0">
          <input class="form-check-input" type="checkbox" name="is_required" id="is_required" value="1" {{ old('is_required')?'checked':'' }}>
          <label class="form-check-label" for="is_required">必須</label>
        </div>
      </div>
    </div>

    <div class="form-check mb-3">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">有効にする</label>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-primary" type="submit">保存</button>
    </div>
  </form>
@endsection
