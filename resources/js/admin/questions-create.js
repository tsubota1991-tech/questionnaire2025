// resources/js/admin/questions-create.js
// 選択式(single/multi)のときだけ、max_select 件ぶんのラベル入力を描画。
// 初回はコントローラ/Bladeから渡された initialOldOptions でプレフィル。
// Laravelのバリデーションエラー（options.*.label / options.min）も反映します。

document.addEventListener('DOMContentLoaded', () => {
  const DEBUG = false; // true にすると console.log が増えます

  const select  = document.querySelector('[name="type"]');
  const countEl = document.querySelector('[name="max_select"]');

  const initialOptions = Array.isArray(window.initialOldOptions) ? window.initialOldOptions : [];
  const initialTypeRaw = typeof window.initialOldType === 'string' ? window.initialOldType : '';
  const initialMax     = Number.isFinite(Number(window.initialOldMax)) ? Number(window.initialOldMax) : null;

  // Blade から渡す想定（無ければ空）
  const optionErrors   = (typeof window.optionErrors === 'object' && window.optionErrors) ? window.optionErrors : {}; // { index: "msg" }
  const optionMinError = typeof window.optionMinError === 'string' ? window.optionMinError : '';

  // 日本語→英語（保険）
  const jpToCode = {
    '単一選択':'single_choice',
    '複数選択':'multi_choice',
    '自由入力':'free_text',
    '数値入力':'number',
    '日付入力':'date',
  };
  const initialType = jpToCode[initialTypeRaw] ?? initialTypeRaw;

  if (DEBUG) console.log('[q-create.js] boot', { initialOptions, initialType, initialMax, optionErrors, optionMinError });

  // 置き場の確保
  let container = document.getElementById('dynamicTexts');
  if (!container) {
    container = document.createElement('div');
    container.id = 'dynamicTexts';
    container.className = 'mb-3';
    (countEl?.closest('.mb-3, .col-md-4') || document.querySelector('form'))?.after(container);
  }

  const isChoiceType = (v) => ['single_choice','multi_choice','単一選択','複数選択'].includes(v);
  let didPrefill = false; // 初回だけ initialOptions を使う

  function makeInvalidFeedback(msg) {
    const fb = document.createElement('div');
    fb.className = 'invalid-feedback';
    fb.textContent = msg || 'この項目は必須です。';
    return fb;
  }

  function render({ prefill = false } = {}) {
    const shouldShow = isChoiceType(select?.value);
    const maxAttr = parseInt(countEl?.getAttribute?.('max') || '50', 10);
    const minAttr = parseInt(countEl?.getAttribute?.('min') || '0', 10);
    const raw = parseInt(countEl?.value || '0', 10);
    const n = Math.min(maxAttr, Math.max(minAttr, Number.isNaN(raw) ? 0 : raw));

    if (DEBUG) console.log('[q-create.js] render()', { selectValue: select?.value, shouldShow, n, prefill, didPrefill });

    if (!shouldShow || n === 0) {
      container.innerHTML = '';
      container.style.display = 'none';
      return;
    }

    // 既入力（prev）か initialOptions か、どちらを使うか
    const prev = Array.from(container.querySelectorAll('input[type="text"]')).map(i => i.value);
    const useInitial = (prefill && !didPrefill && initialOptions.length > 0);
    const source = useInitial ? initialOptions : prev;
    if (DEBUG) console.log('[q-create.js] source =', useInitial ? 'initialOptions' : 'prev', source);

    container.innerHTML = '';
    container.style.display = '';

    // 見出し
    const head = document.createElement('label');
    head.className = 'form-label';
    head.textContent = '選択肢ラベル';
    container.appendChild(head);

    // 配列全体エラー（例: 件数不足 options.min）
    if (optionMinError) {
      const fbAll = document.createElement('div');
      fbAll.className = 'invalid-feedback d-block';
      fbAll.textContent = optionMinError;
      container.appendChild(fbAll);
    }

    // 行の描画
    for (let i = 0; i < n; i++) {
      // 行ラッパ（入力とエラーをまとめる）
      const rowWrap = document.createElement('div');
      rowWrap.className = 'mb-2';

      const input = document.createElement('input');
      input.type = 'text';
      input.name = `options[${i}][label]`;
      input.placeholder = `ラベル ${i + 1}`;
      input.className = 'form-control';
      input.maxLength = 255;
      input.required = false;

      // 初回プレフィル or 既入力の復元
      const val = (source[i] ?? '');
      if (val !== '') input.value = val;

      // サーババリデーションの個別エラー反映
      const errMsg = optionErrors[i];
      if (errMsg) {
        input.classList.add('is-invalid');
        rowWrap.appendChild(input);
        rowWrap.appendChild(makeInvalidFeedback(errMsg));
      } else {
        rowWrap.appendChild(input);
      }

      // クライアント側の簡易必須チェック（入力で即時 is-invalid を外す/付ける）
      input.addEventListener('input', () => {
        const hasVal = input.value.trim() !== '';
        if (!hasVal) {
          input.classList.add('is-invalid');
          // 既存feedbackが無ければ追加
          if (!rowWrap.querySelector('.invalid-feedback')) {
            rowWrap.appendChild(makeInvalidFeedback('この項目は必須です。'));
          }
        } else {
          input.classList.remove('is-invalid');
          const fb = rowWrap.querySelector('.invalid-feedback');
          if (fb) fb.remove();
        }
      });

      container.appendChild(rowWrap);
    }

    if (prefill) didPrefill = true;
  }

  // セレクト未設定なら英語コードで初期化
  if (select && initialType && !select.value) {
    select.value = initialType;
    if (DEBUG) console.log('[q-create.js] select initialized ->', select.value);
  }

  // max_select を件数に合わせて引き上げ（イベントは発火しない）
  if (countEl) {
    const cur = parseInt(countEl.value || '0', 10);
    const desired = Math.max(!Number.isNaN(cur) ? cur : 0, initialOptions.length, Number(initialMax) || 0);
    const maxAttr = parseInt(countEl.getAttribute('max') || '50', 10);
    countEl.value = String(Math.min(desired, maxAttr));
    if (DEBUG) console.log('[q-create.js] max_select normalized =', countEl.value);
  }

  // イベント
  select?.addEventListener('change', () => render());
  countEl?.addEventListener('input', () => render());
  countEl?.addEventListener('change', () => render());

  // 初回：必ず initialOptions でプレフィル
  render({ prefill: true });

  // デバッグ用
  window.dumpQuestionState = () => {
    console.log('[dumpQuestionState]', {
      type: select?.value,
      max: countEl?.value,
      inputs: Array.from(container.querySelectorAll('input[type="text"]')).map(i => i.value),
    });
  };
});
