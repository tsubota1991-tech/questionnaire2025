<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>ログイン</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .card {
      width: 100%;
      max-width: 400px;
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-body p-4">
      <h1 class="h4 mb-4 text-center">ログイン</h1>

      {{-- エラーメッセージ --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">メールアドレス</label>
          <input type="email" id="email" name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}" required autofocus>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">パスワード</label>
          <input type="password" id="password" name="password"
                 class="form-control @error('password') is-invalid @enderror"
                 required>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember"
                 {{ old('remember') ? 'checked' : '' }}>
          <label class="form-check-label" for="remember">ログイン状態を保持</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">ログイン</button>
      </form>
    </div>
  </div>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
