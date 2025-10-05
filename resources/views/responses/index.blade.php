@extends('layouts.admin')
@section('title','回答一覧')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">回答一覧（フォーム #{{ $form->id }}：{{ $form->title }}）</h1>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-outline-dark" href="{{ route('responses.export', $form) }}">エクスポート</a>
      {{-- ★ 古い未提出/無効を削除するボタン（既定：2日 & invalid,in_progress） --}}
      <form method="POST" action="{{ route('responses.purge', $form) }}"
            onsubmit="return confirm('2日前より古い「進行中 / 無効」の回答を削除します。よろしいですか？');">
        @csrf
        <input type="hidden" name="days" value="2">
        <input type="hidden" name="statuses" value="invalid,in_progress">
        <button class="btn btn-outline-danger">古い未提出を削除</button>
      </form>
      <a class="btn btn-secondary" href="{{ route('forms.show', $form) }}">フォーム詳細へ</a>
    </div>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
      <select name="status" class="form-select">
        <option value="">すべてのステータス</option>
        @foreach ($statuses as $st)
          <option value="{{ $st }}" @selected($status===$st)>{{ $statusJa[$st] ?? $st }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-secondary">絞り込み</button>
    </div>
  </form>

  @if ($responses->count() === 0)
    <div class="alert alert-light border">回答がありません。</div>
  @else
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th style="width:280px;">回答ID</th>
            <th style="width:180px;">作成日時</th>
            <th style="width:140px;">ステータス</th>
            <th style="width:220px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($responses as $res)
          <tr>
            <td class="text-monospace">{{ $res->id }}</td>
            <td>
              {{ $res->created_at }}
              {{--
                ※ 日付だけにしたい場合は下記に変更:
                {{ optional($res->created_at)->format('Y-m-d') }}
              --}}
            </td>
            <td>
              <span class="badge bg-{{ $res->status === 'submitted' ? 'success' : ($res->status === 'invalid' ? 'danger' : 'secondary') }}">
                {{ $statusJa[$res->status] ?? $res->status }}
              </span>
            </td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('responses.show', $res) }}">詳細</a>

              {{-- クイックステータス切替（submitted ↔ invalid） --}}
              <form method="POST" action="{{ route('responses.changeStatus', $res) }}" class="d-inline">
                @csrf
                @php
                  $next = $res->status === 'invalid' ? 'submitted' : 'invalid';
                  $nextJa = $statusJa[$next] ?? $next;
                @endphp
                <input type="hidden" name="status" value="{{ $next }}">
                <button class="btn btn-sm btn-outline-warning" type="submit">
                  {{ $res->status === 'invalid' ? '提出済みにする' : '無効にする' }}
                  {{-- もし日本語ラベルで厳密に出したい場合は下記でもOK：
                  変更 → {{ $nextJa }}
                  --}}
                </button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    {{ $responses->links() }}
  @endif
@endsection
