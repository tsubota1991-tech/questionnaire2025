/******/ (() => { // webpackBootstrap
/*!************************************************!*\
  !*** ./resources/js/admin/questions-create.js ***!
  \************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
// resources/js/admin/questions-create.js
// 選択式(single/multi)のときだけ、max_select 件ぶんのラベル入力を描画。
// 初回はコントローラ/Bladeから渡された initialOldOptions でプレフィル。
// Laravelのバリデーションエラー（options.*.label / options.min）も反映します。

document.addEventListener('DOMContentLoaded', function () {
  var _jpToCode$initialType;
  var DEBUG = false; // true にすると console.log が増えます

  var select = document.querySelector('[name="type"]');
  var countEl = document.querySelector('[name="max_select"]');
  var initialOptions = Array.isArray(window.initialOldOptions) ? window.initialOldOptions : [];
  var initialTypeRaw = typeof window.initialOldType === 'string' ? window.initialOldType : '';
  var initialMax = Number.isFinite(Number(window.initialOldMax)) ? Number(window.initialOldMax) : null;

  // Blade から渡す想定（無ければ空）
  var optionErrors = _typeof(window.optionErrors) === 'object' && window.optionErrors ? window.optionErrors : {}; // { index: "msg" }
  var optionMinError = typeof window.optionMinError === 'string' ? window.optionMinError : '';

  // 日本語→英語（保険）
  var jpToCode = {
    '単一選択': 'single_choice',
    '複数選択': 'multi_choice',
    '自由入力': 'free_text',
    '数値入力': 'number',
    '日付入力': 'date'
  };
  var initialType = (_jpToCode$initialType = jpToCode[initialTypeRaw]) !== null && _jpToCode$initialType !== void 0 ? _jpToCode$initialType : initialTypeRaw;
  if (DEBUG) console.log('[q-create.js] boot', {
    initialOptions: initialOptions,
    initialType: initialType,
    initialMax: initialMax,
    optionErrors: optionErrors,
    optionMinError: optionMinError
  });

  // 置き場の確保
  var container = document.getElementById('dynamicTexts');
  if (!container) {
    var _ref;
    container = document.createElement('div');
    container.id = 'dynamicTexts';
    container.className = 'mb-3';
    (_ref = (countEl === null || countEl === void 0 ? void 0 : countEl.closest('.mb-3, .col-md-4')) || document.querySelector('form')) === null || _ref === void 0 || _ref.after(container);
  }
  var isChoiceType = function isChoiceType(v) {
    return ['single_choice', 'multi_choice', '単一選択', '複数選択'].includes(v);
  };
  var didPrefill = false; // 初回だけ initialOptions を使う

  function makeInvalidFeedback(msg) {
    var fb = document.createElement('div');
    fb.className = 'invalid-feedback';
    fb.textContent = msg || 'この項目は必須です。';
    return fb;
  }
  function render() {
    var _countEl$getAttribute, _countEl$getAttribute2;
    var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
      _ref2$prefill = _ref2.prefill,
      prefill = _ref2$prefill === void 0 ? false : _ref2$prefill;
    var shouldShow = isChoiceType(select === null || select === void 0 ? void 0 : select.value);
    var maxAttr = parseInt((countEl === null || countEl === void 0 || (_countEl$getAttribute = countEl.getAttribute) === null || _countEl$getAttribute === void 0 ? void 0 : _countEl$getAttribute.call(countEl, 'max')) || '50', 10);
    var minAttr = parseInt((countEl === null || countEl === void 0 || (_countEl$getAttribute2 = countEl.getAttribute) === null || _countEl$getAttribute2 === void 0 ? void 0 : _countEl$getAttribute2.call(countEl, 'min')) || '0', 10);
    var raw = parseInt((countEl === null || countEl === void 0 ? void 0 : countEl.value) || '0', 10);
    var n = Math.min(maxAttr, Math.max(minAttr, Number.isNaN(raw) ? 0 : raw));
    if (DEBUG) console.log('[q-create.js] render()', {
      selectValue: select === null || select === void 0 ? void 0 : select.value,
      shouldShow: shouldShow,
      n: n,
      prefill: prefill,
      didPrefill: didPrefill
    });
    if (!shouldShow || n === 0) {
      container.innerHTML = '';
      container.style.display = 'none';
      return;
    }

    // 既入力（prev）か initialOptions か、どちらを使うか
    var prev = Array.from(container.querySelectorAll('input[type="text"]')).map(function (i) {
      return i.value;
    });
    var useInitial = prefill && !didPrefill && initialOptions.length > 0;
    var source = useInitial ? initialOptions : prev;
    if (DEBUG) console.log('[q-create.js] source =', useInitial ? 'initialOptions' : 'prev', source);
    container.innerHTML = '';
    container.style.display = '';

    // 見出し
    var head = document.createElement('label');
    head.className = 'form-label';
    head.textContent = '選択肢ラベル';
    container.appendChild(head);

    // 配列全体エラー（例: 件数不足 options.min）
    if (optionMinError) {
      var fbAll = document.createElement('div');
      fbAll.className = 'invalid-feedback d-block';
      fbAll.textContent = optionMinError;
      container.appendChild(fbAll);
    }

    // 行の描画
    var _loop = function _loop() {
      var _source$i;
      // 行ラッパ（入力とエラーをまとめる）
      var rowWrap = document.createElement('div');
      rowWrap.className = 'mb-2';
      var input = document.createElement('input');
      input.type = 'text';
      input.name = "options[".concat(i, "][label]");
      input.placeholder = "\u30E9\u30D9\u30EB ".concat(i + 1);
      input.className = 'form-control';
      input.maxLength = 255;
      input.required = false;

      // 初回プレフィル or 既入力の復元
      var val = (_source$i = source[i]) !== null && _source$i !== void 0 ? _source$i : '';
      if (val !== '') input.value = val;

      // サーババリデーションの個別エラー反映
      var errMsg = optionErrors[i];
      if (errMsg) {
        input.classList.add('is-invalid');
        rowWrap.appendChild(input);
        rowWrap.appendChild(makeInvalidFeedback(errMsg));
      } else {
        rowWrap.appendChild(input);
      }

      // クライアント側の簡易必須チェック（入力で即時 is-invalid を外す/付ける）
      input.addEventListener('input', function () {
        var hasVal = input.value.trim() !== '';
        if (!hasVal) {
          input.classList.add('is-invalid');
          // 既存feedbackが無ければ追加
          if (!rowWrap.querySelector('.invalid-feedback')) {
            rowWrap.appendChild(makeInvalidFeedback('この項目は必須です。'));
          }
        } else {
          input.classList.remove('is-invalid');
          var fb = rowWrap.querySelector('.invalid-feedback');
          if (fb) fb.remove();
        }
      });
      container.appendChild(rowWrap);
    };
    for (var i = 0; i < n; i++) {
      _loop();
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
    var cur = parseInt(countEl.value || '0', 10);
    var desired = Math.max(!Number.isNaN(cur) ? cur : 0, initialOptions.length, Number(initialMax) || 0);
    var maxAttr = parseInt(countEl.getAttribute('max') || '50', 10);
    countEl.value = String(Math.min(desired, maxAttr));
    if (DEBUG) console.log('[q-create.js] max_select normalized =', countEl.value);
  }

  // イベント
  select === null || select === void 0 || select.addEventListener('change', function () {
    return render();
  });
  countEl === null || countEl === void 0 || countEl.addEventListener('input', function () {
    return render();
  });
  countEl === null || countEl === void 0 || countEl.addEventListener('change', function () {
    return render();
  });

  // 初回：必ず initialOptions でプレフィル
  render({
    prefill: true
  });

  // デバッグ用
  window.dumpQuestionState = function () {
    console.log('[dumpQuestionState]', {
      type: select === null || select === void 0 ? void 0 : select.value,
      max: countEl === null || countEl === void 0 ? void 0 : countEl.value,
      inputs: Array.from(container.querySelectorAll('input[type="text"]')).map(function (i) {
        return i.value;
      })
    });
  };
});
/******/ })()
;