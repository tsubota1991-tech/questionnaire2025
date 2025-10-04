<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'アンケート')</title>

  {{-- Bootstrap 5（CDN版・必要なら自前ビルドに差し替え可） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- 任意：全体の余白やタップ領域を少し拡張 --}}
  <style>
    body { background: #f8f9fa; }
    .card { border-radius: 1rem; }
    .form-check-input { width: 1.2em; height: 1.2em; }
    .sticky-actions {
      position: sticky; bottom: 0; z-index: 1020;
      background: #fff; padding: .75rem; border-top: 1px solid rgba(0,0,0,.1);
    }
  </style>
  @stack('head')
</head>
<body>
  <header class="border-bottom bg-white">
    <nav class="navbar navbar-expand-lg container">
      <a class="navbar-brand py-3" href="{{ url('/') }}">アンケート</a>
    </nav>
  </header>

  <main class="container py-4">
    @yield('content')
  </main>

  <footer class="border-top py-4 mt-5">
    <div class="container text-center small text-muted">
      &copy; {{ date('Y') }} Your Company
    </div>
  </footer>

  {{-- Bootstrap Bundle（JS） --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
