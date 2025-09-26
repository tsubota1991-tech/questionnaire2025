@extends('layouts.admin')
@section('title','回答集計')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">回答集計（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-dark" href="{{ route('responses.export', $form) }}">エクスポート</a>
      <a class="btn btn-secondary" href="{{ route('responses.index', $form) }}">回答一覧へ</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">件数（ステータス別）</div>
        <div class="card-body">
          @php
            $statuses = ['submitted','invalid','in_progress'];
          @endphp
          <ul class="list-group">
            @foreach ($statuses as $st)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $st }}
                <span class="badge bg-primary rounded-pill">{{ $counts[$st] ?? 0 }}</span>
              </li>
            @endforeach
            <li class="list-group-item d-flex justify-content-between align-items-center fw-semibold">
              合計
              <span class="badge bg-dark rounded-pill">{{ $total }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    {{-- 必要ならここに「設問別の単純集計」を追加できます --}}
  </div>
@endsection
