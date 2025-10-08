@extends('layouts.admin')

@section('title', $title ?? 'Stub画面')

@section('content')
  <div class="card">
    <div class="card-body">
      <h1 class="h4 mb-3">{{ $title ?? 'Stub画面' }}</h1>

      <ul class="list-unstyled small text-muted mb-3">
        <li>route: <code>{{ $route ?? request()->route()->getName() }}</code></li>
        <li>uri: <code>{{ request()->path() }}</code></li>
        <li>method: <code>{{ request()->method() }}</code></li>
      </ul>

      <pre class="bg-light p-3 rounded">
{{ json_encode($payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
      </pre>

      <a href="{{ route('forms.index') }}" class="btn btn-primary mt-3">フォーム一覧へ戻る</a>
    </div>
  </div>
@endsection
