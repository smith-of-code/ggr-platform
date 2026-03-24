(function () {
  'use strict';

  var script = document.currentScript;
  if (!script) return;

  var slug = script.getAttribute('data-form');
  if (!slug) {
    console.error('[GGR Form Widget] Missing data-form attribute');
    return;
  }

  var srcUrl = new URL(script.src);
  var baseUrl = srcUrl.origin;

  var container = document.createElement('div');
  container.className = 'ggr-form-widget';
  script.parentNode.insertBefore(container, script);

  injectStyles();

  container.innerHTML = '<div class="ggr-fw-loading"><div class="ggr-fw-spinner"></div> Загрузка формы\u2026</div>';

  fetch(baseUrl + '/api/forms/' + encodeURIComponent(slug))
    .then(function (r) {
      if (!r.ok) throw new Error('not_found');
      return r.json();
    })
    .then(function (data) {
      renderForm(data);
    })
    .catch(function () {
      container.innerHTML = '<div class="ggr-fw-error">Не удалось загрузить форму</div>';
    });

  function renderForm(data) {
    var form = data.form;
    var fields = data.fields;
    var html = '<form class="ggr-fw-form" novalidate>';

    html += '<div class="ggr-fw-header">';
    html += '<h2 class="ggr-fw-title">' + esc(form.title) + '</h2>';
    if (form.description) {
      html += '<p class="ggr-fw-desc">' + esc(form.description) + '</p>';
    }
    html += '</div><div class="ggr-fw-fields">';

    fields.forEach(function (f) { html += fieldHtml(f); });

    html += '</div><div class="ggr-fw-footer">';
    html += '<button type="submit" class="ggr-fw-submit">Отправить</button>';
    html += '</div></form>';

    container.innerHTML = html;

    container.querySelector('.ggr-fw-form').addEventListener('submit', function (e) {
      e.preventDefault();
      doSubmit(this, form, fields);
    });
  }

  function fieldHtml(f) {
    var k = attr(f.key);
    var ph = f.placeholder ? ' placeholder="' + attr(f.placeholder) + '"' : '';
    var req = f.required ? ' required' : '';
    var h = '<div class="ggr-fw-field" data-key="' + k + '">';
    h += '<label class="ggr-fw-label">' + esc(f.label);
    if (f.required) h += ' <span class="ggr-fw-req">*</span>';
    h += '</label>';

    switch (f.type) {
      case 'text': case 'email': case 'date':
        h += '<input type="' + f.type + '" name="' + k + '" class="ggr-fw-input"' + ph + req + ' />';
        break;
      case 'phone':
        h += '<input type="tel" name="' + k + '" class="ggr-fw-input"' + ph + req + ' />';
        break;
      case 'number':
        h += '<input type="number" name="' + k + '" class="ggr-fw-input"' + ph + req + ' />';
        break;
      case 'textarea':
        h += '<textarea name="' + k + '" class="ggr-fw-input ggr-fw-textarea" rows="4"' + ph + req + '></textarea>';
        break;
      case 'select':
        h += '<select name="' + k + '" class="ggr-fw-input ggr-fw-select"' + req + '>';
        h += '<option value="">' + esc(f.placeholder || 'Выберите\u2026') + '</option>';
        (f.options || []).forEach(function (o) {
          h += '<option value="' + attr(o) + '">' + esc(o) + '</option>';
        });
        h += '</select>';
        break;
      case 'radio':
        h += '<div class="ggr-fw-opts">';
        (f.options || []).forEach(function (o) {
          h += '<label class="ggr-fw-opt"><input type="radio" name="' + k + '" value="' + attr(o) + '"' + req + ' /><span>' + esc(o) + '</span></label>';
        });
        h += '</div>';
        break;
      case 'checkbox':
        h += '<div class="ggr-fw-opts">';
        (f.options || []).forEach(function (o) {
          h += '<label class="ggr-fw-opt"><input type="checkbox" name="' + k + '[]" value="' + attr(o) + '" /><span>' + esc(o) + '</span></label>';
        });
        h += '</div>';
        break;
      case 'rating':
        h += '<div class="ggr-fw-rating" data-name="' + k + '">';
        h += '<input type="hidden" name="' + k + '" value="" />';
        for (var i = 1; i <= 5; i++) {
          h += '<button type="button" class="ggr-fw-star" data-v="' + i + '">\u2605</button>';
        }
        h += '</div>';
        break;
    }

    h += '<div class="ggr-fw-ferr" data-err="' + k + '"></div></div>';
    return h;
  }

  function doSubmit(formEl, form, fields) {
    container.querySelectorAll('.ggr-fw-ferr').forEach(function (el) { el.textContent = ''; });
    var prev = container.querySelector('.ggr-fw-gerr');
    if (prev) prev.remove();

    var btn = formEl.querySelector('.ggr-fw-submit');
    btn.disabled = true;
    btn.textContent = 'Отправка\u2026';

    var answers = {};
    fields.forEach(function (f) {
      if (f.type === 'checkbox') {
        answers[f.key] = Array.from(formEl.querySelectorAll('input[name="' + f.key + '[]"]:checked')).map(function (c) { return c.value; });
      } else if (f.type === 'rating') {
        answers[f.key] = formEl.querySelector('input[name="' + f.key + '"]').value;
      } else {
        var el = formEl.querySelector('[name="' + f.key + '"]');
        answers[f.key] = el ? el.value : '';
      }
    });

    fetch(baseUrl + '/api/forms/' + encodeURIComponent(slug) + '/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ answers: answers }),
    })
      .then(function (r) {
        if (r.status === 422) {
          return r.json().then(function (d) {
            showFieldErrors(d.errors || {});
            throw { validation: true };
          });
        }
        if (!r.ok) throw new Error('server');
        return r.json();
      })
      .then(function () {
        showSuccess(form.thank_you_message);
      })
      .catch(function (err) {
        if (!err.validation) {
          var e = document.createElement('div');
          e.className = 'ggr-fw-gerr';
          e.textContent = 'Произошла ошибка при отправке';
          formEl.querySelector('.ggr-fw-footer').appendChild(e);
        }
        btn.disabled = false;
        btn.textContent = 'Отправить';
      });
  }

  function showFieldErrors(errors) {
    Object.keys(errors).forEach(function (key) {
      var fk = key.replace('answers.', '');
      var el = container.querySelector('[data-err="' + fk + '"]');
      if (el) el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
    });
  }

  function showSuccess(msg) {
    container.innerHTML =
      '<div class="ggr-fw-success">' +
      '<div class="ggr-fw-ok-icon">\u2713</div>' +
      '<h3 class="ggr-fw-ok-title">Ответ отправлен!</h3>' +
      '<p class="ggr-fw-ok-text">' + esc(msg || 'Спасибо за участие в опросе.') + '</p>' +
      '</div>';
  }

  /* Rating stars — delegated */
  container.addEventListener('click', function (e) {
    var star = e.target.closest('.ggr-fw-star');
    if (!star) return;
    var v = parseInt(star.getAttribute('data-v'), 10);
    var wrap = star.closest('.ggr-fw-rating');
    wrap.querySelector('input[type="hidden"]').value = v;
    wrap.querySelectorAll('.ggr-fw-star').forEach(function (s) {
      s.classList.toggle('ggr-fw-star--on', parseInt(s.getAttribute('data-v'), 10) <= v);
    });
  });

  /* Helpers */
  function esc(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
  }
  function attr(s) {
    if (!s) return '';
    return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  /* Styles */
  function injectStyles() {
    var css =
      '.ggr-form-widget{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;line-height:1.5;color:#1f2937;-webkit-text-size-adjust:100%}' +
      '.ggr-fw-loading{padding:2rem;text-align:center;color:#6b7280;display:flex;align-items:center;justify-content:center;gap:.5rem}' +
      '.ggr-fw-spinner{width:1.25rem;height:1.25rem;border:2px solid #e5e7eb;border-top-color:#3b82f6;border-radius:50%;animation:ggr-spin .6s linear infinite}' +
      '@keyframes ggr-spin{to{transform:rotate(360deg)}}' +
      '.ggr-fw-error{padding:2rem;text-align:center;color:#dc2626;background:#fef2f2;border-radius:.75rem}' +
      '.ggr-fw-form{background:#fff;border:1px solid #e5e7eb;border-radius:1rem;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}' +
      '.ggr-fw-header{padding:1.5rem 2rem;border-bottom:1px solid #f3f4f6}' +
      '.ggr-fw-title{margin:0;font-size:1.375rem;font-weight:700;color:#111827}' +
      '.ggr-fw-desc{margin:.5rem 0 0;font-size:.875rem;color:#6b7280}' +
      '.ggr-fw-fields{padding:1.5rem 2rem;display:flex;flex-direction:column;gap:1.25rem}' +
      '.ggr-fw-field{}' +
      '.ggr-fw-label{display:block;margin-bottom:.375rem;font-size:.875rem;font-weight:500;color:#374151}' +
      '.ggr-fw-req{color:#ef4444}' +
      '.ggr-fw-input{display:block;width:100%;padding:.625rem 1rem;font-size:.875rem;color:#111827;background:#fff;border:1px solid #d1d5db;border-radius:.5rem;outline:none;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}' +
      '.ggr-fw-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}' +
      '.ggr-fw-input::placeholder{color:#9ca3af}' +
      '.ggr-fw-textarea{resize:vertical;min-height:5rem}' +
      '.ggr-fw-select{appearance:auto}' +
      '.ggr-fw-opts{display:flex;flex-direction:column;gap:.5rem}' +
      '.ggr-fw-opt{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem;color:#374151}' +
      '.ggr-fw-opt input{margin:0;accent-color:#3b82f6}' +
      '.ggr-fw-rating{display:flex;gap:.25rem}' +
      '.ggr-fw-star{background:none;border:none;padding:.25rem;font-size:1.75rem;cursor:pointer;color:#d1d5db;line-height:1;transition:color .15s}' +
      '.ggr-fw-star:hover,.ggr-fw-star--on{color:#f59e0b}' +
      '.ggr-fw-footer{padding:1.25rem 2rem;border-top:1px solid #f3f4f6}' +
      '.ggr-fw-submit{display:inline-block;padding:.75rem 2rem;font-size:.875rem;font-weight:600;color:#fff;background:#3b82f6;border:none;border-radius:.75rem;cursor:pointer;transition:background .15s;box-shadow:0 1px 3px rgba(59,130,246,.3)}' +
      '.ggr-fw-submit:hover{background:#2563eb}' +
      '.ggr-fw-submit:disabled{opacity:.5;cursor:not-allowed}' +
      '.ggr-fw-ferr{font-size:.75rem;color:#dc2626;margin-top:.25rem;min-height:0}' +
      '.ggr-fw-gerr{margin-top:.75rem;padding:.625rem 1rem;font-size:.8125rem;color:#dc2626;background:#fef2f2;border-radius:.5rem}' +
      '.ggr-fw-success{text-align:center;padding:3rem 2rem;background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}' +
      '.ggr-fw-ok-icon{width:3.5rem;height:3.5rem;margin:0 auto .75rem;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700}' +
      '.ggr-fw-ok-title{margin:0 0 .5rem;font-size:1.25rem;font-weight:700;color:#111827}' +
      '.ggr-fw-ok-text{margin:0;font-size:.875rem;color:#6b7280}' +
      '@media(max-width:640px){.ggr-fw-header,.ggr-fw-fields,.ggr-fw-footer{padding-left:1.25rem;padding-right:1.25rem}}';

    var el = document.createElement('style');
    el.textContent = css;
    document.head.appendChild(el);
  }
})();
