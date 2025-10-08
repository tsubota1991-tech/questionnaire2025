@extends('layouts.admin')
@section('title','フォームプレビュー')

@section('content')
  <h1 class="h4 mb-3">フォーム プレビュー：{{ $form->title }}</h1>

  <div class="alert alert-secondary">
    ここにプレビューを実装してください（画面・質問の並びを反映）。<br>
    今はダミー表示です。
  </div>

  <a class="btn btn-secondary" href="{{ route('forms.show', $form) }}">詳細へ戻る</a>
@endsection
