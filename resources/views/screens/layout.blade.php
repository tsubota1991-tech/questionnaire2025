@extends('layouts.admin')
@section('title','質問配置')

@section('content')
  <div class="d-flex align-items-center mb-3">
    <h1 class="h4 mb-0">質問配置（画面 #{{ $screen->id }}：{{ $screen->title }}）</h1>
    <a class="btn btn-secondary btn-sm ms-auto" href="{{ route('screens.show', $screen) }}">← 画面詳細へ戻る</a>
  </div>

  <form method="POST" action="{{ route('screen_questions.update', $screen) }}">
    @csrf

    <div class="row g-4">

      {{-- 配置済み --}}
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header d-flex align-items-center">
            <span class="fw-semibold">配置済みの質問</span>
            <span class="ms-2 badge text-bg-secondary">{{ $placed->count() }}</span>
          </div>
          <div class="card-body p-0">
            @if ($placed->isEmpty())
              <div class="p-3 text-muted">まだ質問は配置されていません。</div>
            @else
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width:80px;">順序</th>
                      <th>タイトル</th>
                      <th style="width:140px;">種別</th>
                      <th style="width:100px;">状態</th>
                      <th style="width:80px;">削除</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach ($placed as $row)
                    <tr>
                      <td>
                        <input type="number"
                               name="order[{{ $row->question_id }}]"
                               class="form-control form-control-sm"
                               value="{{ $row->display_order }}" min="1">
                      </td>
                      <td class="fw-semibold">{{ $row->question->title }}</td>
                      <td class="text-muted">
                        {{ $row->question->type_label }}
                      </td>
                      <td>
                        @if($row->question->is_active)
                          <span class="badge bg-success">有効</span>
                        @else
                          <span class="badge bg-secondary">無効</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <input type="checkbox" name="remove[]" value="{{ $row->question_id }}">
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- 未配置 --}}
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header d-flex align-items-center">
            <span class="fw-semibold">未配置の質問（同フォーム内）</span>
            <span class="ms-2 badge text-bg-secondary">{{ $available->count() }}</span>
          </div>
          <div class="card-body p-0">
            @if ($available->isEmpty())
              <div class="p-3 text-muted">追加可能な質問はありません。</div>
            @else
              <div class="list-group list-group-flush">
                @foreach ($available as $q)
                  <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-2" type="checkbox" name="add[]" value="{{ $q->id }}">
                    <div>
                      <div class="fw-semibold">{{ $q->title }}</div>
                      <div class="small text-muted">
                        種別: {{ $q->type_label }}
                        @if(!$q->is_active)
                          <span class="badge bg-secondary ms-2">無効</span>
                        @endif
                      </div>
                    </div>
                  </label>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>

    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-primary" type="submit">配置を保存</button>
      <a class="btn btn-secondary" href="{{ route('screens.show', $screen) }}">キャンセル</a>
    </div>
  </form>
@endsection
