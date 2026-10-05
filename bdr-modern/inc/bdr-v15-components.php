<?php
/**
 * BDR V15 — composants de page partagés (bannière, sections, cartes, étapes, FAQ…).
 * Ils vivaient dans page.php ; les autres gabarits (agences, actualités) les appelaient
 * sans qu'ils soient chargés, ce qui provoquait des erreurs fatales. Ils sont maintenant
 * chargés une fois pour toutes par functions.php.
 */
if (!defined('ABSPATH')) exit;

/** Slug de la page courante (vide pour l'accueil). */
function bdr_v15_current_slug() {
  return function_exists('bdr_v11_get_current_slug') ? bdr_v11_get_current_slug() : sanitize_title(get_post_field('post_name', get_queried_object_id()));
}

/** Déduit un slug de catalogue depuis un chemin ('/credit-auto/', '/fr/credit-auto/'). */
function bdr_v15_slug_from_path($path) {
  $path = trim((string)preg_replace('/[?#].*$/', '', (string)$path), '/');
  if ($path === '') return '';
  $parts = explode('/', $path);
  return sanitize_title(end($parts));
}

/**
 * Bannière de page : volet sombre (titre, introduction, actions) + cadre image à coin en arche.
 * $cta (optionnel) = array(libellé, slug).
 */
function bdr_page_hero($eyebrow, $title, $intro, $dark = false, $cta = null) {
  $lang = function_exists('bdr_v11_lang') ? bdr_v11_lang() : 'fr';
  $slug = bdr_v15_current_slug();
  $img = bdr_v15_image($slug);
  $u = bdr_v11_ui($lang);
  $plain = in_array($slug, array('contact', 'agences', 'mentions-legales', 'donnees-personnelles', 'plan-du-site', 'taux-de-change', 'actualites', 'ouvrir-un-compte', 'banque-en-ligne', 'bdr-net'), true);
  echo '<section class="page-hero" data-page="' . esc_attr($slug) . '"><div class="page-hero-grid"><div class="page-hero-panel"><div class="page-hero-copy">';
  if ($eyebrow !== '') {
    // Les libellés historiques sont en capitales : on les repasse en casse de phrase (hors arabe).
    if ($lang !== 'ar' && function_exists('mb_strtoupper') && $eyebrow === mb_strtoupper($eyebrow, 'UTF-8')) {
      $low = mb_strtolower($eyebrow, 'UTF-8'); $eyebrow = mb_strtoupper(mb_substr($low, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($low, 1, null, 'UTF-8');
    }
    echo '<div class="eyebrow">' . esc_html($eyebrow) . '</div>';
  }
  echo '<h1>' . esc_html($title) . '</h1><p>' . esc_html($intro) . '</p>';
  if ($cta || !$plain) {
    echo '<div class="hero-actions">';
    if ($cta) echo '<a class="btn btn-gold" href="' . esc_url(bdr_v11_url(trim($cta[1], '/'), $lang)) . '">' . esc_html($cta[0]) . ' ' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</a>';
    if (!$plain) {
      echo '<a class="btn btn-gold" href="' . esc_url(bdr_v11_url('contact', $lang)) . '">' . esc_html($u['contact']) . ' ' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</a>';
      echo '<a class="btn btn-ghost" href="' . esc_url(bdr_v11_url('agences', $lang)) . '">' . esc_html($u['branches']) . '</a>';
    }
    echo '</div>';
  }
  echo '</div></div><div class="page-hero-frame">' . bdr_v15_img_tag($img, bdr_v16_photo_alt($img['photo'], isset($lang) ? $lang : bdr_v11_lang()), true, '(min-width: 1024px) 45vw, 100vw') . '</div></div></section>';
}

function bdr_section($title, $intro = '', $class = '') {
  echo '<section class="section ' . esc_attr($class) . '"><div class="container"><div class="section-head"><div><h2>' . esc_html($title) . '</h2>' . ($intro ? '<p class="section-intro">' . esc_html($intro) . '</p>' : '') . '</div></div>';
}
function bdr_close_section() { echo '</div></section>'; }

/**
 * Cartes de navigation/produit. $cards = array(array(kicker, titre, chemin, description), ...).
 * Un chemin vide ou commençant par « # » produit une carte non cliquable (plus de lien mort vers #details).
 */
function bdr_cards($cards) {
  $lang = function_exists('bdr_v11_lang') ? bdr_v11_lang() : 'fr';
  $learn = bdr_v11_ui($lang)['learn'];
  echo '<div class="product-grid detailed-grid">';
  foreach ($cards as $c) {
    $path = isset($c[2]) ? (string)$c[2] : '';
    $label = esc_html($c[0]); $title = esc_html($c[1]); $desc = esc_html($c[3]);
    $is_link = ($path !== '' && $path[0] !== '#');
    $icon = bdr_v15_icon($is_link ? bdr_v15_icon_for(bdr_v15_slug_from_path($path)) : 'check');
    if ($is_link) {
      if (function_exists('bdr_v11_is_lang_route') && bdr_v11_is_lang_route() && $path[0] === '/' && strpos($path, '/en/') !== 0 && strpos($path, '/ar/') !== 0 && strpos($path, '/fr/') !== 0) {
        $path = bdr_v11_url(trim($path, '/'), $lang);
      } elseif ($path[0] === '/' && strpos($path, '/fr/') !== 0 && strpos($path, '/en/') !== 0 && strpos($path, '/ar/') !== 0) {
        $path = bdr_v11_url(trim($path, '/'), $lang);
      }
      echo '<a class="product-card detailed-card" href="' . esc_url($path) . '"><span class="pc-icon">' . $icon . '</span><span class="card-kicker">' . $label . '</span><h3>' . $title . '</h3><p>' . $desc . '</p><span class="card-link">' . esc_html($learn) . '</span></a>';
    } else {
      echo '<div class="product-card detailed-card nonlink-card"><span class="pc-icon">' . $icon . '</span><span class="card-kicker">' . $label . '</span><h3>' . $title . '</h3><p>' . $desc . '</p></div>';
    }
  }
  echo '</div>';
}

function bdr_info_grid($items) {
  echo '<div class="info-grid">';
  foreach ($items as $i) echo '<div class="info-box"><span class="info-mark">' . bdr_v15_icon('check') . '</span><strong>' . esc_html($i[0]) . '</strong><p>' . esc_html($i[1]) . '</p></div>';
  echo '</div>';
}
function bdr_steps($items) {
  echo '<ol class="numbered-steps">'; $n = 1;
  foreach ($items as $i) { echo '<li class="step"><span class="step-n">' . $n . '</span><div><h3>' . esc_html($i[0]) . '</h3><p>' . esc_html($i[1]) . '</p></div></li>'; $n++; }
  echo '</ol>';
}
function bdr_faq($items, $schema = true) {
  if ($schema && function_exists('bdr_v17_faq_collect')) bdr_v17_faq_collect($items);
  echo '<div class="faq-list">';
  foreach ($items as $i) echo '<details><summary>' . esc_html($i[0]) . bdr_v15_icon('chevron', 'ic ic-chev') . '</summary><div><p>' . esc_html($i[1]) . '</p></div></details>';
  echo '</div>';
}
function bdr_related($items) {
  $lang = function_exists('bdr_v11_lang') ? bdr_v11_lang() : 'fr';
  echo '<div class="related-links">';
  foreach ($items as $i) {
    $path = $i[1];
    if (function_exists('bdr_v11_is_lang_route') && bdr_v11_is_lang_route() && strpos($path, '/') === 0 && strpos($path, 'http') !== 0 && !preg_match('#^/(fr|en|ar)/#', $path)) $path = bdr_v11_url(trim($path, '/'), $lang);
    elseif (strpos($path, '/') === 0 && !preg_match('#^/(fr|en|ar)/#', $path)) $path = bdr_v11_url(trim($path, '/'), $lang);
    echo '<a href="' . esc_url($path) . '"><span class="rl-icon">' . bdr_v15_icon(bdr_v15_icon_for(bdr_v15_slug_from_path($path))) . '</span><span class="rl-text"><strong>' . esc_html($i[0]) . '</strong><span>' . esc_html($i[2]) . '</span></span>' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</a>';
  }
  echo '</div>';
}

function bdr_bottom_navigation($slug) {
  $lang = function_exists('bdr_v11_lang') ? bdr_v11_lang() : 'fr';
  $parent = bdr_v11_parent_slug($slug);
  $u = bdr_v11_ui($lang);
  echo '<section class="bdr-page-navigation"><div class="container"><div class="bdr-page-nav-inner">';
  if ($parent) echo '<a class="bdr-nav-back" href="' . esc_url(bdr_v11_url($parent, $lang)) . '">' . bdr_v15_icon('arrow', 'ic ic-arrow ic-back') . ' ' . esc_html(bdr_v11_title($parent, $lang)) . '</a>';
  echo '<button type="button" class="bdr-nav-history" data-fallback="' . esc_url($parent ? bdr_v11_url($parent, $lang) : bdr_v11_url('', $lang)) . '">' . esc_html($u['previous']) . '</button>';
  echo '<a class="bdr-nav-home" href="' . esc_url(bdr_v11_url('', $lang)) . '">' . bdr_v15_icon('home') . ' ' . esc_html($u['home']) . '</a>';
  echo '</div></div></section>';
}

/* ---------------------------------------------------------------------------------- *
 *  Blocs spécifiques
 * ---------------------------------------------------------------------------------- */

/** Tableau des cours de change (accessible, responsive) : achat/vente si disponibles, sinon cours unique. */
function bdr_v15_render_fx_table($lang) {
  $rows = bdr_v15_fx_rows();
  $date = bdr_v15_fx_date($lang);
  $set = function_exists('bdr_v16_fx_dataset') ? bdr_v16_fx_dataset() : array('auto' => false, 'fetched' => 0);
  $two = true; foreach ($rows as $r) if ($r['buy'] === '' || $r['sell'] === '') $two = false;
  $T = function ($fr, $en, $ar) use ($lang) { return bdr_v15_t($fr, $en, $ar, $lang); };

  // Ligne de dates : date du jour (rafraîchie dans le navigateur, donc juste même si la page vient du cache),
  // date des cours, et date/heure (Alger) de la dernière vérification réussie de la source.
  $tz = new DateTimeZone('Africa/Algiers');
  $today = bdr_v17_today_algiers();
  $rates_ymd = (isset($set['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$set['date'])) ? $set['date'] : '';
  $st = get_option('bdr_fx_status', array());
  $checked = !empty($set['auto']) && !empty($st['last_ok']) ? (int)$st['last_ok'] : 0;
  echo '<div class="fx-dateline" data-fx-rates="' . esc_attr($rates_ymd) . '">';
  echo '<span class="fx-today">' . esc_html($T('Aujourd’hui : ', 'Today: ', 'اليوم: ')) . '<strong data-bdr-today="' . esc_attr($lang) . '">' . esc_html(bdr_v17_long_date($today, $lang)) . '</strong></span>';
  if ($rates_ymd) {
    echo '<span class="fx-rates">' . esc_html($T('Cours du ', 'Rates of ', 'أسعار يوم ')) . '<strong>' . esc_html(bdr_v17_long_date($rates_ymd, $lang)) . '</strong>'
      . '<em class="fx-older"' . ($rates_ymd < $today ? '' : ' hidden') . '>' . esc_html($T(' — dernière publication de la Banque d’Algérie', ' — latest publication by the Bank of Algeria', ' — آخر نشر لبنك الجزائر')) . '</em></span>';
  }
  if ($checked) {
    echo '<span class="fx-checked">' . esc_html($T('Vérifiés le ', 'Checked on ', 'تم التحقق يوم ')) . '<strong>' . esc_html(bdr_v17_long_date(wp_date('Y-m-d', $checked, $tz), $lang)) . '</strong>'
      . esc_html($T(' à ', ' at ', ' على الساعة ')) . '<strong dir="ltr">' . esc_html(wp_date('H:i', $checked, $tz)) . '</strong></span>';
  }
  echo '</div>';

  echo '<div class="fx-table-wrap"><table class="fx-table"><caption>' . esc_html($T('Cours indicatifs du dinar algérien', 'Indicative Algerian dinar exchange rates', 'أسعار صرف الدينار الجزائري (للإعلام)')) . ($date ? ' — ' . esc_html($date) : '') . '</caption>';
  echo '<thead><tr><th scope="col">' . esc_html($T('Devise', 'Currency', 'العملة')) . '</th><th scope="col">' . esc_html($T('Code', 'Code', 'الرمز')) . '</th>';
  if ($two) echo '<th scope="col" class="num">' . esc_html($T('Achat (DA)', 'Buy (DZD)', 'شراء (دج)')) . '</th><th scope="col" class="num">' . esc_html($T('Vente (DA)', 'Sell (DZD)', 'بيع (دج)')) . '</th>';
  else echo '<th scope="col" class="num">' . esc_html($T('Cours en DZD', 'Rate in DZD', 'السعر بالدينار')) . '</th>';
  echo '</tr></thead><tbody>';
  foreach ($rows as $r) {
    echo '<tr><th scope="row">' . esc_html($r['names'][$lang]) . '</th><td>' . esc_html($r['code']) . '</td>';
    if ($two) echo '<td class="num" dir="ltr">' . esc_html($r['buy']) . '</td><td class="num" dir="ltr">' . esc_html($r['sell']) . '</td>';
    else echo '<td class="num" dir="ltr">' . esc_html($r['value']) . '</td>';
    echo '</tr>';
  }
  echo '</tbody></table></div>';
  echo '<p class="fx-note"><span>' . esc_html($set['auto']
    ? $T('Source : Banque d’Algérie. Actualisé automatiquement chaque jour.', 'Source: Bank of Algeria. Updated automatically every day.', 'المصدر: بنك الجزائر. يُحدَّث تلقائياً كل يوم.')
    : $T('Cours saisis par la BDR à titre indicatif.', 'Rates entered by BDR for information only.', 'أسعار أدخلها BDR للإعلام فقط.')) . '</span>'
    . (!empty($set['fetched']) ? '<span>' . esc_html($T('Dernière actualisation : ', 'Last updated: ', 'آخر تحديث: ') . wp_date('d/m/Y H:i', (int)$set['fetched'], new DateTimeZone('Africa/Algiers'))) . '</span>' : '') . '</p>';
}

/** Page « Taux de change » (toutes langues). */
function bdr_v15_render_fx_page($lang) {
  $p = bdr_v11_profile('taux-de-change', $lang);
  bdr_page_hero($p['eyebrow'], $p['title'], $p['intro']);
  bdr_section(bdr_v15_t('Cours indicatifs', 'Indicative rates', 'أسعار إرشادية', $lang), bdr_v15_t('Valeurs affichées à titre d’information ; elles ne constituent pas un tarif commercial BDR.', 'Values shown for information only; they are not a BDR commercial rate.', 'القيم المعروضة للإعلام فقط ولا تشكل تسعيرة تجارية لدى BDR.', $lang));
  bdr_v15_render_fx_table($lang);
  bdr_close_section();
  bdr_section(bdr_v15_t('Source officielle', 'Official source', 'المصدر الرسمي', $lang), bdr_v15_t('Consultez les taux de change journaliers publiés par la Banque d’Algérie.', 'See the daily exchange rates published by the Bank of Algeria.', 'اطلع على أسعار الصرف اليومية التي ينشرها بنك الجزائر.', $lang));
  echo '<div class="info-grid"><div class="info-box"><span class="info-mark">' . bdr_v15_icon('exchange') . '</span><strong>' . esc_html(bdr_v15_t('Banque d’Algérie', 'Bank of Algeria', 'بنك الجزائر', $lang)) . '</strong><p><a class="link" href="' . esc_url(bdr_v15_exchange_source()) . '" target="_blank" rel="noopener noreferrer">' . esc_html(bdr_v15_t('Voir les taux de change journaliers', 'View daily exchange rates', 'الاطلاع على الأسعار اليومية', $lang)) . ' →</a></p></div></div>';
  bdr_close_section();
  bdr_v11_localized_bottom('taux-de-change', $lang);
}

/** Page « Actualités » (toutes langues). */
function bdr_v15_render_news_page($lang) {
  $p = bdr_v11_profile('actualites', $lang);
  bdr_page_hero($p['eyebrow'], $p['title'], $p['intro']);
  bdr_section(bdr_v15_t('Actualités de la BDR', 'BDR news', 'أخبار BDR', $lang));
  bdr_v15_render_news($lang);
  bdr_close_section();
  bdr_v11_localized_bottom('actualites', $lang);
}
