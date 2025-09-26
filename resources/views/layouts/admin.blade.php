<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title','管理')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Bootstrap CSS --}}
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
  >
  {{-- Bootstrap Icons --}}
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet"
  >

  <style>
    /* ヘッダーの見栄え微調整 */
    .app-header .brand {
      font-weight: 700;
      letter-spacing: .2px;
    }
    .app-header .form-chip {
      background: var(--bs-primary-bg-subtle);
      border: 1px solid var(--bs-primary-border-subtle);
      color: var(--bs-primary-text);
      border-radius: 999px;
      padding: 4px 10px;
      font-size: .9rem;
      max-width: 42vw;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .app-header .nav-pills .nav-link {
      border-radius: 12px;
      padding: .55rem .9rem;
      font-weight: 600;
    }
    .app-header .nav-pills .nav-link i {
      margin-right: .35rem;
      font-size: 1.05rem;
    }
    .app-header .nav-pills .nav-link.active {
      box-shadow: inset 0 0 0 1px var(--bs-primary-border-subtle);
    }
    /* コンテンツ余白 */
    main.container {
      padding-top: 1rem;
      padding-bottom: 2rem;
    }
  </style>
</head>
<body>
<header class="app-header navbar navbar-expand-lg bg-light border-bottom">
  <div class="container-fluid">
    {{-- 左：ブランド & フォーム名チップ --}}
    <a class="navbar-brand brand" href="{{ route('forms.index') }}">
      <i class="bi bi-speedometer2 me-1"></i> 管理
    </a>

    @if(!empty($navForm))
      <span class="form-chip ms-2" title="{{ $navForm->title }}">
        <i class="bi bi-ui-checks-grid me-1"></i>
        {{ \Illuminate\Support\Str::limit($navForm->title, 40) }}
      </span>
    @endif

    {{-- トグル（スマホ） --}}
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false">
      <span class="navbar-toggler-icon"></span>
    </button>

    {{-- 中央：大きめボタンナビ --}}
    <div id="mainNav" class="collapse navbar-collapse">
      <ul class="navbar-nav nav nav-pills mx-lg-3 my-2 my-lg-0 gap-2">
        {{-- 常に表示：フォーム一覧 --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('forms.*') ? 'active' : '' }}"
             href="{{ route('forms.index') }}">
            <i class="bi bi-list-task"></i> フォーム一覧
          </a>
        </li>

        {{-- navForm が特定できる時だけ表示 --}}
        @if(!empty($navForm))
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('forms.screens.*') || request()->routeIs('screens.*') ? 'active' : '' }}"
               href="{{ route('forms.screens.index', $navForm) }}">
              <i class="bi bi-columns-gap"></i> 画面一覧
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('forms.questions.*') || request()->routeIs('questions.*') ? 'active' : '' }}"
               href="{{ route('forms.questions.index', $navForm) }}">
              <i class="bi bi-question-square"></i> 質問一覧
            </a>
          </li>
        @endif
      </ul>

      {{-- 右：ログアウト --}}
      <div class="ms-lg-auto">
        <form class="d-flex" method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-box-arrow-right me-1"></i> ログアウト
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<main class="container">
  @if (session('status'))
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>{{ session('status') }}</div>
  @endif

  @yield('content')
</main>

{{-- Bootstrap JS Bundle --}}
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
  crossorigin="anonymous"
></script>

@stack('scripts')
</body>
</html>
