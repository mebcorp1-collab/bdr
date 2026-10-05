/* BDR-NET — small enhancements. Everything works without JavaScript. */
(function () {
  'use strict';

  // Show / hide the password.
  document.querySelectorAll('[data-bdr-net-eye]').forEach(function (btn) {
    var input = document.getElementById(btn.getAttribute('data-bdr-net-eye'));
    if (!input) return;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', btn.getAttribute(show ? 'data-label-hide' : 'data-label-show'));
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      var use = btn.querySelector('use');
      if (use) use.setAttribute('href', show ? '#ic-eye-off' : '#ic-eye');
    });
  });

  // Message thread: show the latest messages.
  document.querySelectorAll('.bdr-ec-thread').forEach(function (t) { t.scrollTop = t.scrollHeight; });

  // Avoid double submissions.
  document.querySelectorAll('.bdr-net-form, .bdr-ec-form').forEach(function (form) {
    form.addEventListener('submit', function () {
      var b = form.querySelector('[type="submit"]');
      if (b) setTimeout(function () { b.disabled = true; }, 0);
    });
  });
})();
