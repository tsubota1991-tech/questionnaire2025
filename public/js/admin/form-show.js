/******/ (() => { // webpackBootstrap
/*!*****************************************!*\
  !*** ./resources/js/admin/form-show.js ***!
  \*****************************************/
// 管理: フォーム詳細画面（公開URLのコピー）
document.addEventListener('click', function (e) {
  var btn = e.target.closest('[data-copy]');
  if (!btn) return;
  var text = btn.getAttribute('data-copy') || '';
  if (!text) return;
  var done = function done() {
    var orig = btn.textContent;
    btn.textContent = 'コピーしました';
    btn.disabled = true;
    setTimeout(function () {
      btn.textContent = orig;
      btn.disabled = false;
    }, 1500);
  };

  // クリップボードAPI優先
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done)["catch"](function () {
      return fallback();
    });
    return;
  }

  // フォールバック
  function fallback() {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand('copy');
    } catch (_unused) {}
    document.body.removeChild(ta);
    done();
  }
  fallback();
});
/******/ })()
;