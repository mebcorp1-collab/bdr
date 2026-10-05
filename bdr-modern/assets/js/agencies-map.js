/* BDR — carte des agences (Leaflet + OpenStreetMap). Données lues dans <script type="application/json" id="bdr-agencies">. */
(function () {
  'use strict';
  var T = {
    fr: { code: 'Code agence', osm: 'Voir sur OpenStreetMap', none: 'Aucune agence trouvée.', unit: 'agence(s)', err: 'La carte est temporairement indisponible.' },
    en: { code: 'Branch code', osm: 'View on OpenStreetMap', none: 'No branch found.', unit: 'branch(es)', err: 'The map is temporarily unavailable.' },
    ar: { code: 'رمز الوكالة', osm: 'عرض على OpenStreetMap', none: 'لم يتم العثور على وكالة.', unit: 'وكالة', err: 'الخريطة غير متاحة مؤقتاً.' }
  };
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]; }); }
  function osm(a) { return 'https://www.openstreetmap.org/?mlat=' + encodeURIComponent(a.lat) + '&mlon=' + encodeURIComponent(a.lng) + '#map=16/' + encodeURIComponent(a.lat) + '/' + encodeURIComponent(a.lng); }

  function init() {
    var el = document.getElementById('bdr-agency-map'); if (!el) return;
    var lang = (document.documentElement.lang || 'fr').slice(0, 2), i18n = T[lang] || T.fr;
    var all = [];
    try { var n = document.getElementById('bdr-agencies'); all = JSON.parse(n ? n.textContent : '[]') || []; } catch (e) {}
    var hasMap = !!window.L;

    var search = document.getElementById('bdr-agency-filter'), select = document.getElementById('bdr-agency-wilaya'),
        list = document.getElementById('bdr-agency-list'), count = document.getElementById('bdr-agency-count');
    var map = null, group = null, markers = [];
    if (hasMap) {
      el.innerHTML = '';
      map = L.map(el, { scrollWheelZoom: false }).setView([34.0, 3.0], 5);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(map);
      map.on('click', function () { map.scrollWheelZoom.enable(); });
      group = L.layerGroup().addTo(map);
    } else {
      el.innerHTML = '<div class="map-error">' + esc(i18n.err) + '</div>'; /* la liste et les filtres restent utilisables */
    }
    if (select) {
      var seen = {};
      all.forEach(function (a) { if (a.wilaya && !seen[a.wilaya]) seen[a.wilaya] = 1; });
      Object.keys(seen).sort(function (x, y) { return x.localeCompare(y, 'fr'); }).forEach(function (w) { var o = document.createElement('option'); o.value = w; o.textContent = w; select.appendChild(o); });
    }
    all.forEach(function (a) {
      var lat = Number(a.lat), lng = Number(a.lng); if (!isFinite(lat) || !isFinite(lng)) return;
      var m = hasMap ? L.marker([lat, lng], { title: a.title }) : { getLatLng: function () { return null; } };
      if (hasMap) m.bindPopup('<div class="bdr-map-info"><strong>' + esc(a.title) + '</strong><div>' + esc(a.adresse) + '</div><div>' + esc(a.ville) + ' — ' + esc(a.wilaya) + '</div><div>' + esc(i18n.code) + ' : <b>' + esc(a.code) + '</b></div><div dir="ltr">' + esc(a.tel) + '</div><a target="_blank" rel="noopener" href="' + osm(a) + '">' + esc(i18n.osm) + '</a></div>');
      m._a = a; markers.push(m);
    });

    function apply() {
      var q = (search && search.value || '').toLowerCase().trim(), w = (select && select.value || '').toLowerCase(), shown = [];
      if (group) group.clearLayers();
      markers.forEach(function (m) {
        var a = m._a, hay = [a.title, a.wilaya, a.ville, a.code, a.adresse].join(' ').toLowerCase();
        if ((!q || hay.indexOf(q) >= 0) && (!w || String(a.wilaya).toLowerCase() === w)) { if (group) group.addLayer(m); shown.push(m); }
      });
      if (list) {
        list.innerHTML = shown.slice(0, 80).map(function (m) {
          var a = m._a;
          return '<a class="bdr-agency-list-item" href="' + esc(a.url || '#') + '" data-id="' + esc(a.id) + '"><strong>' + esc(a.title) + '</strong><br><span>' + esc(a.ville) + ' — ' + esc(a.wilaya) + '</span><br><small>' + esc(a.adresse) + '</small></a>';
        }).join('') || '<p class="search-empty">' + esc(i18n.none) + '</p>';
      }
      if (count) count.textContent = shown.length + ' ' + i18n.unit;
      if (map && shown.length) map.fitBounds(L.latLngBounds(shown.map(function (m) { return m.getLatLng(); })), { padding: [30, 30], maxZoom: 12 });
    }
    if (search) search.addEventListener('input', apply);
    if (select) select.addEventListener('change', apply);
    if (list) list.addEventListener('click', function (e) {
      var item = e.target.closest('[data-id]'); if (!item || !map) return;
      e.preventDefault();
      var m = markers.filter(function (x) { return String(x._a.id) === String(item.getAttribute('data-id')); })[0];
      if (m && map) { map.setView(m.getLatLng(), 15, { animate: true }); m.openPopup(); el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
    });
    apply();
    if (map) setTimeout(function () { map.invalidateSize(); }, 250);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
