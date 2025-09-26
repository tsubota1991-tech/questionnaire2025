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
            <th>タイトル</th>
            <th style="width:120px;">状態</th>
            <th style="width:280px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($forms as $form)
          <tr>
            <td class="text-muted">{{ $form->id }}</td>
            <td>
              <div class="fw-semibold">{{ $form->title }}</div>
              <div class="text-muted small text-truncate" style="max-width:520px;">
                {{ $form->description }}
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
              <a class="btn btn-sm btn-outline-primary" href="{{ route('forms.show', $form) }}">詳細</a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('forms.edit', $form) }}">編集</a>
              <a class="btn btn-sm btn-dark" href="{{ route('forms.screens.index', $form) }}">画面一覧</a>
              {{-- 質問一覧への直リンクは出さない（フロー統一のため） --}}
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{ $forms->links() }}
  @endif
@endsection
