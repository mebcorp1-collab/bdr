<?php
/**
 * BDR V17 — SEO Google.
 * Sitemap XML multilingue (hreflang), robots.txt, titres et descriptions calibrés, image Open Graph par page,
 * données structurées enrichies (WebPage typée, FinancialProduct, FAQPage, ImageObject).
 * Inactif si un plugin SEO (Yoast, Rank Math, AIOSEO, SEOPress) est actif.
 */
if (!defined('ABSPATH')) exit;

/* ---------- Titres : mot-clé + marque, 50-62 caractères visés ---------- */
function bdr_v17_title($slug, $lang) {
  $brand = array('fr' => 'BDR Algérie', 'en' => 'BDR Algeria', 'ar' => 'بنك BDR الجزائر');
  $home = array(
    'fr' => 'BDR – Banque de Développement Régional | Comptes, crédits, épargne en Algérie',
    'en' => 'BDR – Regional Development Bank | Accounts, loans and savings in Algeria',
    'ar' => 'BDR – بنك التنمية الجهوية | حسابات وتمويلات وادخار في الجزائر',
  );
  if ($slug === '') return $home[$lang];
  $t = bdr_v11_title($slug, $lang);
  if ($lang === 'fr' && function_exists('bdr_v10_page_title_map')) {
    $m = bdr_v10_page_title_map();
    if (isset($m[$slug])) { $t2 = $m[$slug]; if (mb_strlen($t2) <= 62) return $t2; }
  }
  $long = array('fr' => 'BDR – Banque de Développement Régional', 'en' => 'BDR – Regional Development Bank', 'ar' => 'BDR – بنك التنمية الجهوية');
  $full = $t . ' | ' . $long[$lang];
  if (mb_strlen($full) > 66) $full = $t . ' | ' . $brand[$lang];
  if (mb_strlen($full) > 66) $full = $t . ' | BDR';
  return $full;
}
function bdr_v17_filter_title($title) {
  if (bdr_v10_seo_plugin_active() || is_admin()) return $title;
  if (is_singular('bdr_actualite') || is_singular('bdr_agence') || is_404() || is_search()) return $title;
  if (!get_query_var('bdr_lang') && !get_query_var('bdr_front') && !is_front_page() && !is_page()) return $title;
  $lang = bdr_v11_lang(); $slug = function_exists('bdr_v11_get_current_slug') ? bdr_v11_get_current_slug() : '';
  $cat = bdr_v11_catalog();
  if ($slug !== '' && !isset($cat[$slug])) return $title;
  return bdr_v17_title($slug, $lang);
}
add_filter('pre_get_document_title', 'bdr_v17_filter_title', 200);

/** Coupe proprement à la limite d'un mot. */
function bdr_v17_trim($s, $max = 158) {
  $s = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($s)));
  if (mb_strlen($s) <= $max) return $s;
  $cut = mb_substr($s, 0, $max - 1); $sp = mb_strrpos($cut, ' ');
  if ($sp !== false && $sp > $max * 0.6) $cut = mb_substr($cut, 0, $sp);
  return rtrim($cut, " ,;:.—-") . '…';
}
function bdr_v17_desc($slug, $lang) {
  if ($slug === '') return bdr_v15_desc_home($lang);
  if ($lang === 'fr') { $m = bdr_v10_description_map(); if (isset($m[$slug])) return $m[$slug]; }
  $p = bdr_v11_profile($slug, $lang);
  $intro = trim((string)($p['intro'] ?? '')); $t = bdr_v11_title($slug, $lang);
  $tail = array('fr' => ' — BDR, Banque de Développement Régional (Algérie).', 'en' => ' — BDR, Regional Development Bank (Algeria).', 'ar' => ' — بنك التنمية الجهوية BDR (الجزائر).');
  $d = $intro !== '' ? $t . ' : ' . $intro : $t;
  if ($lang === 'en') $d = $intro !== '' ? $t . ': ' . $intro : $t;
  if ($lang === 'ar') $d = $intro !== '' ? $t . ': ' . $intro : $t;
  if (mb_strlen($d) < 110) $d .= $tail[$lang];
  return bdr_v17_trim($d, 158);
}

/* ---------- Image Open Graph 1200×630 par page ---------- */
function bdr_v17_og_image($slug) {
  $name = 'headquarters';
  if ($slug !== '' && function_exists('bdr_v15_image')) { $im = bdr_v15_image($slug); if (!empty($im['photo'])) $name = $im['photo']; }
  elseif ($slug === '') $name = 'headquarters';
  $rel = '/assets/images/og/' . $name . '.jpg';
  if (!file_exists(get_template_directory() . $rel)) return array('url' => get_template_directory_uri() . '/assets/images/og-default.jpg', 'w' => 1200, 'h' => 630, 'photo' => $name);
  return array('url' => get_template_directory_uri() . $rel, 'w' => 1200, 'h' => 630, 'photo' => $name);
}

/* ---------- Collecte des FAQ rendues (→ FAQPage en pied de page) ---------- */
function bdr_v17_faq_collect($items = null) {
  static $all = array();
  if ($items === null) return $all;
  foreach ($items as $i) if (!empty($i[0]) && !empty($i[1])) $all[] = array((string)$i[0], (string)$i[1]);
  return $all;
}
function bdr_v17_footer_jsonld() {
  if (is_admin() || bdr_v10_seo_plugin_active()) return;
  $faq = bdr_v17_faq_collect(); if (!$faq) return;
  $c = bdr_v15_page_context(); $seen = array(); $q = array();
  foreach ($faq as $f) {
    if (isset($seen[$f[0]])) continue; $seen[$f[0]] = 1;
    $q[] = array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f[1]));
  }
  if (!$q) return;
  echo '<script type="application/ld+json">' . wp_json_encode(array('@context' => 'https://schema.org', '@type' => 'FAQPage', '@id' => $c['url'] . '#faq', 'inLanguage' => $c['lang'], 'mainEntity' => $q), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>' . "\n";
}
add_action('wp_footer', 'bdr_v17_footer_jsonld', 50);

/** Type de page schema.org et produit financier, selon la rubrique. */
function bdr_v17_page_type($slug) {
  if ($slug === 'contact') return 'ContactPage';
  if (in_array($slug, array('la-banque', 'gouvernance', 'engagements'), true)) return 'AboutPage';
  if (in_array($slug, array('agences', 'actualites', 'plan-du-site'), true)) return 'CollectionPage';
  return 'WebPage';
}
function bdr_v17_is_product($slug) {
  if ($slug === '') return false;
  $p = bdr_v11_profile($slug, 'fr');
  return $slug !== 'comptes-cartes' && in_array($p['group'] ?? 'general', array('card', 'credit', 'saving', 'islamic', 'account', 'trade', 'business', 'digital'), true) && !in_array($slug, array('particuliers', 'entreprises', 'cartes-bancaires', 'credits', 'financement', 'epargne', 'placements', 'finance-islamique', 'commerce-exterieur', 'banque-en-ligne', 'services-distance'), true);
}

/* ---------- robots.txt ---------- */
function bdr_v17_robots_txt($output, $public) {
  if (!$public) return $output;
  $l = array(
    'User-agent: *',
    'Disallow: /wp-admin/', 'Allow: /wp-admin/admin-ajax.php',
    'Disallow: /wp-login.php', 'Disallow: /?s=', 'Disallow: /*?s=', 'Disallow: /search/',
    'Disallow: /*?replytocom=',
    '',
    'Sitemap: ' . home_url('/sitemap.xml'),
  );
  return implode("\n", $l) . "\n";
}
add_filter('robots_txt', 'bdr_v17_robots_txt', 50, 2);

/* ---------- Sitemap XML multilingue : /sitemap.xml ---------- */
add_filter('wp_sitemaps_enabled', '__return_false');
function bdr_v17_sitemap_xml() {
  $noindex = array(); // pages techniques : aucune aujourd'hui (les pages utiles restent toutes indexables)
  $cat = bdr_v11_catalog(); $langs = array('ar', 'fr', 'en');
  $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
  $x .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
  $slugs = array_merge(array(''), array_values(array_filter(array_keys($cat), function ($s) { return $s !== ''; })));
  $prio = function ($s) {
    if ($s === '') return '1.0';
    if (in_array($s, array('particuliers', 'entreprises', 'credits', 'epargne', 'cartes-bancaires', 'finance-islamique', 'banque-en-ligne', 'agences', 'ouvrir-un-compte', 'commerce-exterieur', 'financement'), true)) return '0.9';
    if (in_array($s, array('mentions-legales', 'donnees-personnelles', 'plan-du-site'), true)) return '0.3';
    return '0.7';
  };
  foreach ($slugs as $s) {
    if (in_array($s, $noindex, true)) continue;
    $img = bdr_v17_og_image($s);
    foreach ($langs as $l) {
      $x .= '  <url><loc>' . esc_url(bdr_v11_url($s, $l)) . '</loc>';
      foreach ($langs as $l2) $x .= '<xhtml:link rel="alternate" hreflang="' . $l2 . '" href="' . esc_url(bdr_v11_url($s, $l2)) . '"/>';
      $x .= '<xhtml:link rel="alternate" hreflang="x-default" href="' . esc_url(bdr_v11_url($s, 'ar')) . '"/>';
      $x .= '<changefreq>' . ($s === '' || $s === 'actualites' ? 'weekly' : ($s === 'taux-de-change' ? 'daily' : 'monthly')) . '</changefreq><priority>' . $prio($s) . '</priority>';
      $x .= '<image:image><image:loc>' . esc_url($img['url']) . '</image:loc></image:image></url>' . "\n";
    }
  }
  $q = new WP_Query(array('post_type' => 'bdr_actualite', 'post_status' => 'publish', 'posts_per_page' => 500, 'no_found_rows' => true, 'orderby' => 'date', 'order' => 'DESC'));
  foreach ($q->posts as $p) $x .= '  <url><loc>' . esc_url(get_permalink($p)) . '</loc><lastmod>' . esc_html(get_the_modified_date('c', $p)) . '</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>' . "\n";
  return $x . '</urlset>' . "\n";
}
function bdr_v17_serve_sitemap() {
  $path = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '';
  $base = rtrim((string)parse_url(home_url('/'), PHP_URL_PATH), '/');
  if ($path !== $base . '/sitemap.xml') return;
  status_header(200);
  header('Content-Type: application/xml; charset=UTF-8'); header('X-Robots-Tag: noindex, follow'); header('Cache-Control: public, max-age=3600');
  echo bdr_v17_sitemap_xml(); exit;
}
add_action('init', 'bdr_v17_serve_sitemap', 1);
// /sitemap.xml ne doit pas être redirigé par WordPress.
add_filter('redirect_canonical', function ($r) { $p = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : ''; return substr($p, -12) === '/sitemap.xml' ? false : $r; });
