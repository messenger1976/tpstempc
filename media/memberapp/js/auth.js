/* TAPSTEMCO Member Loan App - sign-in / activation pages. */
(function (w, d) {
  'use strict';

  var MA = w.MA || {};

  if (MA.clearLocal && w.MAStore) {
    w.MAStore.clearAll();
  }
  if (w.MAStore) {
    w.MAStore.registerServiceWorker(MA.base);
  }

  d.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentNode.querySelector('input');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.textContent = show ? 'Hide' : 'Show';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  d.querySelectorAll('[data-busy-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('[type=submit]');
      if (btn) {
        btn.disabled = true;
        btn.classList.add('is-busy');
      }
    });
  });

  d.querySelectorAll('.ma-otp').forEach(function (input) {
    input.addEventListener('input', function () {
      input.value = input.value.replace(/\D/g, '').slice(0, 6);
      if (input.value.length === 6 && input.form && input.form.hasAttribute('data-busy-form') && input.form.querySelectorAll('input:not([type=hidden])').length === 1) {
        input.form.requestSubmit ? input.form.requestSubmit() : input.form.submit();
      }
    });
  });

  var resend = d.querySelector('[data-resend]');
  if (resend) {
    var left = parseInt(resend.getAttribute('data-wait'), 10) || 60;
    var label = resend.textContent;
    resend.disabled = true;
    var t = setInterval(function () {
      left -= 1;
      resend.textContent = label + ' (' + left + 's)';
      if (left <= 0) {
        clearInterval(t);
        resend.disabled = false;
        resend.textContent = label;
      }
    }, 1000);
  }

  var strength = d.querySelector('[data-strength]');
  if (strength) {
    var bar = d.querySelector('.ma-strength i');
    strength.addEventListener('input', function () {
      var v = strength.value;
      var score = 0;
      if (v.length >= 8) score++;
      if (v.length >= 12) score++;
      if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
      if (/\d/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      bar.style.width = (score * 20) + '%';
      bar.className = score <= 2 ? 'is-weak' : (score === 3 ? 'is-ok' : 'is-strong');
    });
  }

  // Install banner (Android/desktop Chrome). iOS users get a hint instead.
  var banner = d.getElementById('maInstall');
  var deferred = null;
  var dismissed = w.MAStore && w.MAStore.prefs().installDismissed;
  w.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    if (banner && !dismissed) {
      banner.hidden = false;
    }
  });
  if (banner) {
    banner.querySelector('[data-install]').addEventListener('click', function () {
      if (deferred) {
        deferred.prompt();
        deferred.userChoice.finally(function () { banner.hidden = true; deferred = null; });
      }
    });
    banner.querySelector('[data-install-dismiss]').addEventListener('click', function () {
      banner.hidden = true;
      if (w.MAStore) {
        w.MAStore.setPrefs({ installDismissed: Date.now() });
      }
    });
  }
})(window, document);
