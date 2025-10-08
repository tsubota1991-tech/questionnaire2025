// 管理: フォーム詳細画面（公開URLのコピー）
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-copy]');
  if (!btn) return;

  const text = btn.getAttribute('data-copy') || '';
  if (!text) return;

  const done = () => {
    const orig = btn.textContent;
    btn.textContent = 'コピーしました';
    btn.disabled = true;
    setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 1500);
  };

  // クリップボードAPI優先
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done).catch(() => fallback());
    return;
  }

  // フォールバック
  function fallback() {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); } catch {}
    document.body.removeChild(ta);
    done();
  }
  fallback();
});
