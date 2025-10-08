@extends('layouts.admin')
@section('title','画面一覧')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">画面一覧（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>
    <div class="ms-auto d-flex gap-2">
      <a href="{{ route('forms.screens.create', $form) }}" class="btn btn-primary">＋ 画面追加</a>
    </div>
  </div>

  <div class="mb-3 d-flex gap-2">
    <a class="btn btn-secondary btn-sm" href="{{ route('forms.show', $form) }}">← フォーム詳細へ戻る</a>
    <a class="btn btn-outline-secondary btn-sm" href="{{ route('forms.index') }}">フォーム一覧へ</a>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-sm-6 col-md-4">
      <input type="text" name="q" class="form-control" placeholder="タイトルで検索" value="{{ $q }}">
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-secondary">検索</button>
    </div>
  </form>
  @error('display_order')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror
  @if ($screens->count() === 0)
    <div class="alert alert-light border">このフォームに紐づく画面はありません。「画面追加」から作成してください。</div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>タイトル</th>
            <th style="width:120px;">順序</th>
            <th style="width:120px;">状態</th>
            <th style="width:320px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($screens as $screen)
          <tr>
            <td class="text-muted">{{ $screen->id }}</td>
            <td class="fw-semibold">{{ $screen->title }}</td>
            <td>{{ $screen->display_order }}</td>
            <td>
              @if($screen->is_active)
                <span class="badge bg-success">有効</span>
              @else
                <span class="badge bg-secondary">無効</span>
              @endif
            </td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('screens.show', $screen) }}">詳細</a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('screens.edit', $screen) }}">編集</a>
              <a class="btn btn-sm btn-outline-dark" href="{{ route('screen_questions.edit', $screen) }}">質問配置</a>
              <form method="POST" action="{{ route('screens.reorder', $screen) }}" class="d-inline-flex align-items-center gap-1">
                @csrf @method('PATCH')
                <input type="number" name="display_order" class="form-control form-control-sm" style="width:90px" value="{{ $screen->display_order }}" min="0">
                <button class="btn btn-sm btn-outline-info" type="submit">順序保存</button>
              </form>
              <form method="POST" action="{{ route('screens.destroy', $screen) }}" class="d-inline" onsubmit="return confirm('削除しますか？');">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">削除</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{ $screens->links() }}
  @endif
@endsection
