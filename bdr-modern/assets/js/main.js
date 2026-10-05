/* BDR — interactions du thème. Aucun script en ligne : la configuration arrive par <script type="application/json" id="bdr-config">. */
(function () {
  'use strict';

  var cfg = { ajax: '', lang: 'fr', home: '/', i18n: {} };
  try { var node = document.getElementById('bdr-config'); if (node) cfg = Object.assign(cfg, JSON.parse(node.textContent)); } catch (e) {}
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var lang = cfg.lang || (document.documentElement.lang || 'fr').slice(0, 2);
  var FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, summary, [tabindex]:not([tabindex="-1"])';

  /* ---------- Méga-menu (bouton chevron = accès clavier/tactile ; le survol est géré en CSS) ---------- */
  function closeMegas(except) {
    $$('.nav-item.is-open').forEach(function (it) {
      if (it === except) return;
      it.classList.remove('is-open');
      var b = $('.nav-toggle', it); if (b) b.setAttribute('aria-expanded', 'false');
    });
  }
  $$('.nav-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.nav-item');
      var open = !item.classList.contains('is-open');
      closeMegas(item);
      item.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', function (e) { if (!e.target.closest('.nav-item')) closeMegas(); });

  /* ---------- Tiroir mobile ---------- */
  var drawer = $('#bdr-drawer'), backdrop = $('#bdr-drawer-backdrop'), burger = $('#bdr-burger');
  function openDrawer() {
    if (!drawer) return;
    drawer.hidden = false; if (backdrop) backdrop.hidden = false;
    document.body.classList.add('drawer-open');
    if (burger) burger.setAttribute('aria-expanded', 'true');
    var f = $('#bdr-drawer-close'); if (f) f.focus();
  }
  function closeDrawer(focusBack) {
    if (!drawer || drawer.hidden) return;
    drawer.hidden = true; if (backdrop) backdrop.hidden = true;
    document.body.classList.remove('drawer-open');
    if (burger) { burger.setAttribute('aria-expanded', 'false'); if (focusBack) burger.focus(); }
  }
  if (burger) burger.addEventListener('click', openDrawer);
  var dClose = $('#bdr-drawer-close'); if (dClose) dClose.addEventListener('click', function () { closeDrawer(true); });
  if (backdrop) backdrop.addEventListener('click', function () { closeDrawer(true); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 1024) closeDrawer(false); });
  if (drawer) drawer.addEventListener('keydown', function (e) {
    if (e.key !== 'Tab') return;
    var f = $$(FOCUSABLE, drawer).filter(function (x) { return x.offsetParent !== null; });
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  /* ---------- Recherche ---------- */
  var overlay = $('#bdr-search-overlay'), input = $('#bdr-search-input'), results = $('#bdr-search-results');
  var openBtns = $$('#bdr-search-open, #bdr-search-open-m'), openBtn = openBtns[0], lastOpener = null, closeBtn = $('#bdr-search-close'), timer = null, ctrl = null;
  function setMsg(text) {
    results.textContent = '';
    var d = document.createElement('div'); d.className = 'search-empty'; d.textContent = text; results.appendChild(d);
  }
  function openSearch() {
    if (!overlay) return;
    closeDrawer(false);
    overlay.hidden = false; overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('drawer-open');
    if (input) { input.value = ''; setTimeout(function () { input.focus(); }, 20); }
  }
  function closeSearch() {
    if (!overlay || overlay.hidden) return;
    overlay.hidden = true; overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('drawer-open');
    var back = (lastOpener && lastOpener.offsetParent !== null) ? lastOpener : (openBtns.filter(function (b) { return b.offsetParent !== null; })[0]);
    if (back) back.focus();
  }
  function runSearch(q) {
    if (!results) return;
    q = q.trim();
    if (q.length < 2) { setMsg(cfg.i18n.min || ''); return; }
    if (!cfg.ajax) { setMsg(cfg.i18n.fail || ''); return; }
    if (ctrl && ctrl.abort) ctrl.abort();
    ctrl = window.AbortController ? new AbortController() : null;
    setMsg(cfg.i18n.busy || '…');
    fetch(cfg.ajax + '?action=bdr_site_search&q=' + encodeURIComponent(q) + '&lang=' + encodeURIComponent(lang), { credentials: 'same-origin', signal: ctrl ? ctrl.signal : undefined })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        var list = data && data.success && data.data ? data.data : [];
        if (!list.length) { setMsg(cfg.i18n.none || ''); return; }
        results.textContent = '';
        list.forEach(function (it) {
          var a = document.createElement('a'); a.className = 'search-result'; a.href = it.url;
          var s = document.createElement('small'); s.textContent = it.type || '';
          var t = document.createElement('strong'); t.textContent = it.title || '';
          var x = document.createElement('span'); x.textContent = it.excerpt || '';
          a.appendChild(s); a.appendChild(t); a.appendChild(x); results.appendChild(a);
        });
      })
      .catch(function (err) { if (err && err.name === 'AbortError') return; setMsg(cfg.i18n.fail || ''); });
  }
  openBtns.forEach(function (b) { b.addEventListener('click', function () { lastOpener = b; openSearch(); }); });
  if (closeBtn) closeBtn.addEventListener('click', closeSearch);
  if (overlay) overlay.addEventListener('click', function (e) { if (e.target === overlay) closeSearch(); });
  if (input) input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { runSearch(input.value); }, 200); });
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key && e.key.toLowerCase() === 'k') { e.preventDefault(); openSearch(); }
    if (e.key === 'Escape') {
      closeSearch(); closeDrawer(true);
      var op = $('.nav-item.is-open'); if (op) { var b = $('.nav-toggle', op); closeMegas(); if (b) b.focus(); }
    }
  });

  /* ---------- Bouton « page précédente » ---------- */
  $$('.bdr-nav-history').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var same = false;
      try { same = document.referrer && new URL(document.referrer, location.href).origin === location.origin; } catch (e) {}
      if (same && document.referrer !== location.href && history.length > 1) { history.back(); return; }
      location.href = btn.getAttribute('data-fallback') || cfg.home || '/';
    });
  });

  /* ---------- Carrousel d'accueil ---------- */
  var hero = $('#bdr-hero');
  if (hero) {
    var slides = $$('.hero-slide', hero), dots = $$('.hero-dot', hero), playBtn = $('.hero-play', hero);
    var idx = 0, playing = true, tick = null, DELAY = 6500;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var show = function (n) {
      idx = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        var on = i === idx;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        $$('a', s).forEach(function (a) { if (on) a.removeAttribute('tabindex'); else a.setAttribute('tabindex', '-1'); });
      });
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); if (i === idx) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
    };
    var stop = function () { if (tick) { clearInterval(tick); tick = null; } };
    var start = function () { stop(); if (playing && !reduce && slides.length > 1) tick = setInterval(function () { show(idx + 1); }, DELAY); };
    var setPlaying = function (p) {
      playing = p;
      if (playBtn) {
        playBtn.setAttribute('data-state', p ? 'playing' : 'paused');
        var use = $('use', playBtn); if (use) use.setAttribute('href', p ? '#ic-pause' : '#ic-play');
        playBtn.setAttribute('aria-label', p ? (cfg.i18n.pause || 'Pause') : (cfg.i18n.play || 'Play'));
      }
      start();
    };
    dots.forEach(function (d) { d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-go'), 10) || 0); start(); }); });
    var prev = $('.hero-prev', hero), next = $('.hero-next', hero);
    var rtl = document.documentElement.dir === 'rtl';
    if (prev) prev.addEventListener('click', function () { show(idx - 1); start(); });
    if (next) next.addEventListener('click', function () { show(idx + 1); start(); });
    if (playBtn) playBtn.addEventListener('click', function () { setPlaying(!playing); });
    hero.addEventListener('mouseenter', stop); hero.addEventListener('mouseleave', start);
    hero.addEventListener('focusin', stop); hero.addEventListener('focusout', start);
    var x0 = null;
    hero.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0; x0 = null;
      if (Math.abs(dx) > 50) { show(idx + ((dx < 0) !== rtl ? 1 : -1)); start(); }
    }, { passive: true });
    document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
    if (reduce) setPlaying(false); else start();
  }

  /* ---------- Galerie panoramique ---------- */
  var rail = $('#bdr-pano-rail');
  if (rail) {
    var panos = $$('.pano', rail), pcount = $('#bdr-pano-count');
    var rtlDoc = document.documentElement.dir === 'rtl';
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var pStep = function () {
      if (!panos.length) return 0;
      var gap = parseFloat(getComputedStyle(rail).columnGap || getComputedStyle(rail).gap) || 16;
      return panos[0].getBoundingClientRect().width + gap;
    };
    var pCur = function () { var st = pStep(); return st ? Math.min(panos.length - 1, Math.round(Math.abs(rail.scrollLeft) / st)) : 0; };
    var pUpdate = function () { if (pcount) pcount.textContent = (pCur() + 1) + ' / ' + panos.length; };
    var pGo = function (dir) {
      var i = Math.max(0, Math.min(panos.length - 1, pCur() + dir));
      rail.scrollTo({ left: (rtlDoc ? -1 : 1) * i * pStep(), behavior: reduceMotion ? 'auto' : 'smooth' });
    };
    var pPrev = $('.pano-prev'), pNext = $('.pano-next');
    if (pPrev) pPrev.addEventListener('click', function () { pGo(-1); });
    if (pNext) pNext.addEventListener('click', function () { pGo(1); });
    var pt = null; rail.addEventListener('scroll', function () { clearTimeout(pt); pt = setTimeout(pUpdate, 60); }, { passive: true });
    window.addEventListener('resize', pUpdate);
    pUpdate();
  }

  /* ---------- Connexion client : captcha serveur puis envoi direct au portail officiel ---------- */
  var form = $('#bdr-login');
  if (form && cfg.login) {
    var L = cfg.login, M = L.msg || {};
    var cImg = $('#bdr-cap-img'), cQ = $('#bdr-cap-q'), cWait = $('#bdr-cap-wait'), cInput = $('#bdr-l-cap'), lMsg = $('#bdr-l-msg'), lGo = $('#bdr-l-go');
    var cMode = $('#bdr-cap-mode'), cRefresh = $('#bdr-cap-refresh'), hp = $('#bdr-l-hp');
    var token = '', kind = 'image', busy = false;
    var say = function (text, cls) { lMsg.textContent = text || ''; lMsg.className = 'login-msg' + (cls ? ' ' + cls : ''); lMsg.hidden = !text; };
    var loadCaptcha = function () {
      token = ''; cInput.value = ''; cImg.hidden = true; cQ.hidden = true; cWait.hidden = false; cWait.textContent = M.loading || '…';
      fetch(cfg.ajax + '?action=bdr_captcha_new&kind=' + kind + '&lang=' + encodeURIComponent(lang), { credentials: 'same-origin', cache: 'no-store' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (!d || !d.success) { cWait.textContent = (d && d.data && d.data.code === 'rate') ? M.rate : M.net; return; }
          token = d.data.token; cWait.hidden = true;
          if (d.data.kind === 'text') { cQ.textContent = d.data.question; cQ.hidden = false; cInput.setAttribute('inputmode', 'numeric'); cInput.setAttribute('autocapitalize', 'off'); }
          else { cImg.src = d.data.image; cImg.hidden = false; cInput.setAttribute('inputmode', 'text'); cInput.setAttribute('autocapitalize', 'characters'); }
        })
        .catch(function () { cWait.textContent = M.net || ''; });
    };
    if (cRefresh) cRefresh.addEventListener('click', function () { loadCaptcha(); cInput.focus(); });
    if (cMode) cMode.addEventListener('click', function () { kind = kind === 'image' ? 'text' : 'image'; cMode.textContent = kind === 'image' ? M.toText : M.toImg; loadCaptcha(); cInput.focus(); });
    var eye = $('#bdr-l-eye'), pass = $('#bdr-l-pass');
    if (eye && pass) eye.addEventListener('click', function () {
      var show = pass.type === 'password'; pass.type = show ? 'text' : 'password';
      var use = $('use', eye); if (use) use.setAttribute('href', show ? '#ic-eye-off' : '#ic-eye');
      eye.setAttribute('aria-label', show ? M.hide : M.show);
    });
    var proceed = function (u, p) {
      if (L.mode === 'direct' && L.post) {
        // Les champs reçoivent leur attribut « name » uniquement ici, une fois le code validé.
        u.name = L.user; p.name = L.pass;
        form.setAttribute('action', L.post); form.setAttribute('method', 'post'); form.setAttribute('autocomplete', 'off');
        HTMLFormElement.prototype.submit.call(form);
      } else if (L.portal) { location.href = L.portal; }
    };
    form.addEventListener('submit', function (e) {
      e.preventDefault(); if (busy) return;
      var u = $('[data-role="user"]', form), p = $('[data-role="pass"]', form);
      if (L.mode === 'direct' && (!u || !p || !u.value.trim() || !p.value)) { say(M.empty); (u && !u.value.trim() ? u : p || cInput).focus(); return; }
      if (!cInput.value.trim()) { say(M.emptyc); cInput.focus(); return; }
      if (!token) { say(M.net); return; }
      busy = true; lGo.disabled = true; say(M.sending, 'is-warn');
      var body = new URLSearchParams(); body.set('action', 'bdr_captcha_check'); body.set('token', token); body.set('answer', cInput.value); body.set('website', hp ? hp.value : '');
      fetch(cfg.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.success) {
            if (L.mode === 'off') { say(M.off, 'is-warn'); busy = false; lGo.disabled = false; cInput.value = ''; loadCaptcha(); return; }
            say(M.ok, 'is-ok'); proceed(u, p); return;
          }
          busy = false; lGo.disabled = false;
          say(d && d.data && d.data.code === 'rate' ? M.rate : M.wrong); loadCaptcha(); cInput.focus();
        })
        .catch(function () { busy = false; lGo.disabled = false; say(M.net); });
    });
    loadCaptcha();
  }

  /* ---------- Simulateur de mensualité (indicatif) ---------- */
  var a = $('#bdr-sim-amount'), t = $('#bdr-sim-term'), r = $('#bdr-sim-rate'), o = $('#bdr-sim-result');
  function calc() {
    if (!a || !t || !r || !o) return;
    var P = parseFloat(a.value) || 0, n = parseInt(t.value, 10) || 0, i = (parseFloat(r.value) || 0) / 1200;
    if (P <= 0 || n <= 0) { o.textContent = '—'; return; }
    var m = i ? P * i / (1 - Math.pow(1 + i, -n)) : P / n;
    var loc = lang === 'ar' ? 'ar-DZ' : (lang === 'en' ? 'en-GB' : 'fr-FR');
    o.textContent = new Intl.NumberFormat(loc, { maximumFractionDigits: 0 }).format(m) + (lang === 'ar' ? ' دج / شهر' : (lang === 'en' ? ' DZD / month' : ' DA / mois'));
  }
  [a, t, r].forEach(function (e) { if (e) e.addEventListener('input', calc); });
  calc();
})();
