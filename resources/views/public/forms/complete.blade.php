@extends('layouts.public')
@section('title', $form->title.' - 完了')
@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
      <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5 text-center">
          <h1 class="h4 mb-3">{{ $form->title }}</h1>
          <div class="display-6 mb-3">✅</div>
          <p>ご回答ありがとうございました。送信が完了しました。<br>下記のボタンを押して画面を閉じることができます。</p>

          <button type="button" id="closeBtn" class="btn btn-primary mt-3">画面を閉じる</button>

          <script>
            document.addEventListener('DOMContentLoaded', function () {
              const closeBtn = document.getElementById('closeBtn');
              const fallbackUrl = @json(route('public.forms.landing', $form->public_path));

              function tryCloseWindow() {
                // 1) そのまま close を試す（スクリプトで開いたウィンドウなら閉じられる）
                window.close();
                if (window.closed) return true;

                // 2) opener がある場合は self を上書きしてから close を再試行（対策パターン）
                if (window.opener && !window.opener.closed) {
                  try { window.opener = null; } catch (_) {}
                  window.open('', '_self');
                  window.close();
                  if (window.closed) return true;
                }

                return false;
              }

              function fallbackNavigate() {
                // 3) 履歴があれば戻る、なければランディングへ
                if (history.length > 1) {
                  history.back();
                } else {
                  location.replace(fallbackUrl);
                }
              }

              closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const closed = tryCloseWindow();
                if (!closed) fallbackNavigate();
              });
            });
          </script>
        </div>
      </div>
    </div>
  </div>
@endsection
