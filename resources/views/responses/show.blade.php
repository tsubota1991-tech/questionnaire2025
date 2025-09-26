@extends('layouts.admin')
@section('title','回答詳細')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">回答詳細</h1>
    <div class="ms-auto">
      <a class="btn btn-secondary btn-sm" href="{{ route('responses.index', $form) }}">← 回答一覧へ</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="text-muted small">回答ID</div>
          <div class="text-monospace">{{ $response->id }}</div>
        </div>
        <div class="col-md-4">
          <div class="text-muted small">作成日時</div>
          <div>{{ $response->created_at }}</div>
        </div>
        <div class="col-md-4">
          <div class="text-muted small">ステータス</div>
          <div>
            <span class="badge bg-{{ $response->status === 'submitted' ? 'success' : ($response->status === 'invalid' ? 'danger' : 'secondary') }}">
              {{ $response->status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ステータス変更 --}}
  <form method="POST" action="{{ route('responses.changeStatus', $response) }}" class="mb-4">
    @csrf
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">ステータス変更</label>
        <select name="status" class="form-select">
          <option value="submitted" @selected($response->status==='submitted')>submitted</option>
          <option value="invalid" @selected($response->status==='invalid')>invalid</option>
          <option value="in_progress" @selected($response->status==='in_progress')>in_progress</option>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-outline-warning">更新</button>
      </div>
    </div>
  </form>

  {{-- 回答アイテム --}}
  <div class="card">
    <div class="card-header">回答内容</div>
    <div class="card-body p-0">
      @if ($items->isEmpty())
        <div class="p-3 text-muted">回答内容が見つかりません。</div>
      @else
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">QID</th>
                <th>設問</th>
                <th style="width:160px;">種別</th>
                <th>値</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($items as $it)
                <tr>
                  <td>{{ $it->question_id }}</td>
                  <td>{{ optional($it->question)->title }}</td>
                  <td class="text-muted">{{ optional($it->question)->type }}</td>
                  <td>
                    @if ($it->selected_option_id)
                      {{-- 選択肢のラベルを優先表示（無ければvalue/ID） --}}
                      {{ optional($it->option)->label ?? $it->selected_option_id }}
                      @if(optional($it->option)->value)
                        <span class="text-muted small"> ({{ optional($it->option)->value }})</span>
                      @endif
                    @elseif (!is_null($it->numeric_value))
                      {{ $it->numeric_value }}
                    @elseif (!is_null($it->date_value))
                      {{ $it->date_value }}
                    @else
                      {!! nl2br(e($it->free_text)) !!}
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
@endsection
