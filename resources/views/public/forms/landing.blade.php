@extends('layouts.public')
@section('title', $form->title.' - 入口')
@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-7">
      <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
          <h1 class="h3 mb-3">{{ $form->title }}</h1>
          @if($form->description)
            <p class="text-muted">{{ $form->description }}</p>
          @endif

          <div class="d-flex align-items-center gap-3 my-3">
            <span class="badge bg-primary-subtle text-primary border border-primary">画面数 {{ $screens->count() }}</span>
            <span class="badge bg-success-subtle text-success border border-success">{{ $form->is_active ? '公開中' : '停止中' }}</span>
          </div>

          <hr class="my-4">

          <form method="POST" action="{{ route('public.forms.start', $form->public_path) }}">
            @csrf
            <div class="d-grid d-md-flex gap-2">
              <button class="btn btn-primary btn-lg px-4" type="submit">開始する</button>
              <a class="btn btn-outline-secondary" href="javascript:history.back()">戻る</a>
            </div>
          </form>

          <p class="text-muted small mt-3 mb-0">所要時間には個人差があります。途中で中断した場合はブラウザの戻る/更新にご注意ください。</p>
        </div>
      </div>
    </div>
  </div>
@endsection
