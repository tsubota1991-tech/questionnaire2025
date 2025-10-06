@extends('layouts.admin')
@section('title','質問一覧')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">質問一覧（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>
    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('forms.questions.create', $form) }}" class="btn btn-primary">＋ 質問追加</a>
    </div>
  </div>

  <div class="mb-3 d-flex gap-2">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.show', $form) }}">← フォーム詳細へ戻る</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('forms.index') }}">フォーム一覧へ</a>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
      <input type="text" name="q" class="form-control" placeholder="質問文・補足で検索" value="{{ $q }}">
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-secondary">検索</button>
    </div>
  </form>

  @if ($questions->count() === 0)
    <div class="alert alert-light border">質問がありません。「質問追加」から作成してください。</div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>質問文</th>
            <th style="width:140px;">種別</th>
            <th style="width:100px;">順序</th>
            <th style="width:120px;">状態</th>
            <th style="width:360px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($questions as $q)
          <tr>
            <td class="text-muted">{{ $q->id }}</td>
            <td>
              <div class="fw-semibold">{{ $q->title }}</div>
              @if($q->help_text)
                <div class="small text-muted text-truncate" style="max-width:520px;">{{ $q->help_text }}</div>
              @endif
            </td>
            <td class="text-muted">{{ $q->type_label }}</td>
            <td>{{ $q->display_order }}</td>
            <td>
              @if($q->is_active)
                <span class="badge bg-success">有効</span>
              @else
                <span class="badge bg-secondary">無効</span>
              @endif
            </td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('questions.show', $q) }}">詳細</a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('questions.edit', $q) }}">編集</a>
              <form method="POST" action="{{ route('questions.reorder', $q) }}" class="d-inline-flex align-items-center gap-1">
                @csrf @method('PATCH')
                <input type="number" name="display_order" class="form-control form-control-sm" style="width:90px" value="{{ $q->display_order }}" min="1">
                <button class="btn btn-sm btn-outline-info" type="submit">順序保存</button>
              </form>
              <form method="POST" action="{{ route('questions.destroy', $q) }}" class="d-inline" onsubmit="return confirm('削除しますか？');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">削除</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{ $questions->links() }}
  @endif
@endsection
