@extends('layouts.admin')
@section('title', '画面詳細')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">
      画面詳細 #{{ $screen->id }}
      <span class="text-muted fs-6">（フォーム：{{ $form->title }}）</span>
    </h1>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary"
         href="{{ route('forms.screens.index', $form) }}">
        一覧へ戻る
      </a>
    </div>
  </div>

  {{-- 画面の基本情報 --}}
  <div class="card mb-4">
    <div class="card-header">基本情報</div>
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">画面タイトル</dt>
        <dd class="col-sm-9">{{ $screen->title }}</dd>

        <dt class="col-sm-3">表示順序</dt>
        <dd class="col-sm-9">{{ $screen->display_order }}</dd>

        <dt class="col-sm-3">状態</dt>
        <dd class="col-sm-9">
          @if($screen->is_active)
            <span class="badge bg-success">有効</span>
          @else
            <span class="badge bg-secondary">無効</span>
          @endif
        </dd>

        <dt class="col-sm-3">所属フォーム</dt>
        <dd class="col-sm-9">#{{ $form->id }}：{{ $form->title }}</dd>

        <dt class="col-sm-3">作成日時 / 更新日時</dt>
        <dd class="col-sm-9">
          {{ $screen->created_at }} / {{ $screen->updated_at }}
        </dd>
      </dl>
    </div>
  </div>

  {{-- 現在この画面に登録されている質問一覧 --}}
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span>現在登録されている質問（この画面に配置済み）</span>
      <a class="btn btn-sm btn-primary" href="{{ route('screen_questions.edit', $screen) }}">
        配置を編集
      </a>
    </div>
    <div class="card-body p-0">
      @if($placed->isEmpty())
        <div class="p-4 text-muted">この画面に配置されている質問はありません。</div>
      @else
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 90px;">表示順</th>
                <th>質問タイトル</th>
                <th style="width: 140px;">種別</th>
                <th style="width: 100px;">必須</th>
                <th style="width: 100px;">状態</th>
                <th style="width: 140px;">質問ID</th>
              </tr>
            </thead>
            <tbody>
              @foreach($placed as $row)
                @php
                  $q = $row->question;
                @endphp
                <tr>
                  <td>{{ $row->display_order }}</td>
                  <td class="text-break">{{ $q?->title ?? '（削除済み）' }}</td>
                  <td>
                    @switch($q?->type)
                      @case('single_choice') 単一選択 @break
                      @case('multi_choice')  複数選択 @break
                      @case('free_text')     自由記述 @break
                      @case('number')        数値 @break
                      @case('date')          日付 @break
                      @default               -
                    @endswitch
                  </td>
                  <td>
                    @if($q?->is_required)
                      <span class="badge bg-danger">必須</span>
                    @else
                      <span class="badge bg-secondary">任意</span>
                    @endif
                  </td>
                  <td>
                    @if($q?->is_active)
                      <span class="badge bg-success">有効</span>
                    @else
                      <span class="badge bg-secondary">無効</span>
                    @endif
                  </td>
                  <td>#{{ $q?->id ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
@endsection
