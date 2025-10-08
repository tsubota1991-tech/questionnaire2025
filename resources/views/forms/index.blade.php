@extends('layouts.admin') 
@section('title','フォーム一覧')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">フォーム一覧</h1>
    <a href="{{ route('forms.create') }}" class="btn btn-primary ms-auto">＋ 新規作成</a>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
      <input type="text" name="q" class="form-control" placeholder="タイトル・説明で検索" value="{{ $q }}">
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-secondary">検索</button>
    </div>
  </form>

  @if ($forms->count() === 0)
    <div class="alert alert-light border">フォームがありません。まずは「新規作成」してください。</div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>タイトル / 公開URL</th>
            <th style="width:120px;">状態</th>
            <th style="width:260px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($forms as $form)
          @php
            $publicUrl = route('public.forms.landing', $form->public_path);
          @endphp
          <tr>
            <td class="text-muted">{{ $form->id }}</td>
            <td>
              <div class="fw-semibold">{{ $form->title }}</div>
              <div class="text-muted small text-truncate" style="max-width:520px;">
                {{ $form->description }}
              </div>
              {{-- 公開URL（回答画面リンク） --}}
              <div class="small mt-1">
                <span class="text-muted me-1">回答URL:</span>
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="text-break">
                  {{ \Illuminate\Support\Str::limit($publicUrl, 80) }}
                </a>
                @unless($form->is_active)
                  <span class="badge bg-secondary ms-1">無効</span>
                @endunless
              </div>
            </td>
            <td>
              @if($form->is_active)
                <span class="badge bg-success">有効</span>
              @else
                <span class="badge bg-secondary">無効</span>
              @endif
            </td>
            <td class="text-nowrap">
              <div class="btn-group me-1 position-static">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  設定
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ route('forms.show', $form) }}">詳細</a></li>
                  <li><a class="dropdown-item" href="{{ route('forms.edit', $form) }}">編集</a></li>
                  <li><a class="dropdown-item" href="{{ route('forms.screens.index', $form) }}">画面一覧</a></li>
                  <li><a class="dropdown-item" href="{{ route('forms.questions.index', $form) }}">質問一覧</a></li>
                </ul>
              </div>
              <div class="btn-group position-static">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  回答
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ route('responses.index', $form) }}">回答一覧</a></li>
                  <li><a class="dropdown-item" href="{{ route('responses.export', $form) }}">エクスポート</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="{{ $publicUrl }}" target="_blank" rel="noopener">回答ページを開く</a></li>
                </ul>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{ $forms->links() }}
  @endif
@endsection
