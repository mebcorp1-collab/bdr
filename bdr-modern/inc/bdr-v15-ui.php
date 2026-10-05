<?php
/**
 * BDR V15 — couche interface : icônes, navigation unique, images par page,
 * bascule de langue fiable, taux de change, statistiques réseau.
 * Toutes les données de navigation passent ici : le méga-menu, le tiroir mobile,
 * le pied de page et le plan du site lisent la même structure.
 */
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------ *
 *  Réglages modifiables depuis Apparence > Personnaliser > BDR – Coordonnées
 * ------------------------------------------------------------------ */
function bdr_v15_opt($key, $default = '') {
  $defaults = array(
    'phone'       => '05 65 32 11 09',
    'phone_raw'   => '+213565321109',
    'email'       => 'contact@bdr-dz.com',
    'ebanking'    => '',   // URL de la page de connexion officielle BDR-NET : à renseigner
    'ebanking_post' => '', // adresse d'envoi (action) du formulaire de connexion BDR-NET : fournie par l'éditeur
    'ebanking_user' => 'username',
    'ebanking_pass' => 'password',
    'facebook'    => '',
    'linkedin'    => '',
    'youtube'     => '',
    'instagram'   => '',
    'fx_date'     => '2026-09-22',
  );
  $val = get_theme_mod('bdr_' . $key, '');
  if ($val === '' || $val === null) $val = isset($defaults[$key]) ? $defaults[$key] : $default;
  return $val;
}

function bdr_v15_customizer($wp_customize) {
  $wp_customize->add_section('bdr_contact', array('title' => 'BDR – Coordonnées & liens', 'priority' => 31));
  $fields = array(
    'phone'     => 'Téléphone affiché (ex. 05 65 32 11 09)',
    'phone_raw' => 'Téléphone au format international (ex. +213565321109)',
    'email'     => 'E-mail de contact',
    'ebanking'  => 'BDR-NET — adresse de la page de connexion officielle (https)',
    'ebanking_post' => 'BDR-NET — adresse d’envoi du formulaire (https, optionnel : active la connexion directe depuis le site)',
    'ebanking_user' => 'BDR-NET — nom du champ « identifiant » (ex. username)',
    'ebanking_pass' => 'BDR-NET — nom du champ « mot de passe » (ex. password)',
    'facebook'  => 'Facebook (URL)',
    'linkedin'  => 'LinkedIn (URL)',
    'youtube'   => 'YouTube (URL)',
    'instagram' => 'Instagram (URL)',
    'fx_date'   => 'Date des cours de change affichés (AAAA-MM-JJ)',
  );
  foreach ($fields as $k => $label) {
    $is_url = in_array($k, array('ebanking', 'ebanking_post', 'facebook', 'linkedin', 'youtube', 'instagram'), true);
    $wp_customize->add_setting('bdr_' . $k, array(
      'default' => '',
      'sanitize_callback' => $is_url ? 'esc_url_raw' : 'sanitize_text_field',
    ));
    $wp_customize->add_control('bdr_' . $k, array('label' => $label, 'section' => 'bdr_contact', 'type' => $is_url ? 'url' : 'text'));
  }
}
add_action('customize_register', 'bdr_v15_customizer');

/* ------------------------------------------------------------------ *
 *  Icônes (sprite SVG inline, tracé fin façon CPA)
 * ------------------------------------------------------------------ */
function bdr_v15_icons() {
  return array(
    'user-plus' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M19 8v6M16 11h6"/>',
    'home'      => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v10h13V10"/><path d="M10 20v-6h4v6"/>',
    'ship'      => '<path d="M3 17l1.6 3h14.8L21 17H3z"/><path d="M6 17v-4h12v4"/><path d="M9 13V9h6v4"/><path d="M12 9V5"/>',
    'crescent'  => '<path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5z"/><path d="M17 4.5l.7 1.6 1.7.2-1.3 1.1.4 1.7-1.5-.9-1.5.9.4-1.7-1.3-1.1 1.7-.2z"/>',
    'wheat'     => '<path d="M12 21V8"/><path d="M12 8c0-3 1.8-5 4-5 0 3-1.5 5-4 5z"/><path d="M12 13c0-2.6-1.8-4.2-4-4.2 0 2.7 1.6 4.2 4 4.2z"/><path d="M12 17.5c0-2.6 1.8-4.2 4-4.2 0 2.7-1.6 4.2-4 4.2z"/><path d="M12 17.5c0-2.6-1.8-4-4-4 0 2.6 1.6 4 4 4z"/>',
    'card'      => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19"/><path d="M6 15h4"/>',
    'coins'     => '<ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6"/><path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/>',
    'doc'       => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M9 12h6M9 16h6"/>',
    'phone'     => '<rect x="7" y="2.5" width="10" height="19" rx="2.5"/><path d="M11 18.5h2"/>',
    'pin'       => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    'calc'      => '<rect x="5" y="2.5" width="14" height="19" rx="2.5"/><rect x="8" y="5.5" width="8" height="3.5"/><path d="M8.5 13h.01M12 13h.01M15.5 13h.01M8.5 17h.01M12 17h.01M15.5 17h.01"/>',
    'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3.5 7l8.5 6.5L20.5 7"/>',
    'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.5 2.5 3.8 5.5 3.8 9S14.5 18.5 12 21c-2.5-2.5-3.8-5.5-3.8-9S9.5 5.5 12 3z"/>',
    'search'    => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l5 5"/>',
    'bank'      => '<path d="M3 9.5 12 4l9 5.5"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8"/><path d="M3 20.5h18"/>',
    'exchange'  => '<path d="M4 8h14l-3-3"/><path d="M20 16H6l3 3"/>',
    'car'       => '<path d="M5 15l1.5-5h11L19 15"/><rect x="3" y="15" width="18" height="4" rx="1.5"/><path d="M7 19v1.5M17 19v1.5"/>',
    'shield'    => '<path d="M12 3l8 3v6c0 4.5-3.3 7.8-8 9-4.7-1.2-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5L15.5 10"/>',
    'users'     => '<circle cx="9" cy="8" r="3.3"/><path d="M2.5 19.5c0-3.3 2.8-5.5 6.5-5.5s6.5 2.2 6.5 5.5"/><path d="M16 5a3.3 3.3 0 0 1 0 6.4"/><path d="M18 14.3c2 .6 3.5 2.2 3.5 5.2"/>',
    'safe'      => '<rect x="3" y="4" width="18" height="15" rx="2"/><circle cx="12" cy="11.5" r="3.5"/><path d="M12 11.5l1.5-1.5"/><path d="M7 19v2M17 19v2"/>',
    'bell'      => '<path d="M6 16v-5a6 6 0 1 1 12 0v5l1.5 2h-15z"/><path d="M10 21h4"/>',
    'lock'      => '<rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
    'fish'      => '<path d="M3 12c3-4 7-5 10-5 3 0 5.5 2 8 5-2.5 3-5 5-8 5-3 0-7-1-10-5z"/><circle cx="16.5" cy="11" r=".6"/>',
    'factory'   => '<path d="M3 21V10l6 4v-4l6 4V6h3v15z"/><path d="M3 21h18"/>',
    'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2"/><path d="M3 13h18"/>',
    'chart'     => '<path d="M4 20V4"/><path d="M4 20h16"/><path d="M8 16l4-5 3 3 4-6"/>',
    'city'      => '<path d="M4 21V8l6-3v16"/><path d="M10 21V11l10 3v7"/><path d="M3 21h18"/>',
    'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'close'     => '<path d="M6 6l12 12M18 6L6 18"/>',
    'chevron'   => '<path d="M6 9l6 6 6-6"/>',
    'arrow'     => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
    'check'     => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    'download'  => '<path d="M12 4v11"/><path d="M7 11l5 5 5-5"/><path d="M5 20h14"/>',
    'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'key'       => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9"/><path d="M16 7l3 3"/>',
    'leaf'      => '<path d="M5 19c0-9 5-14 14-14 0 9-5 14-14 14z"/><path d="M5 19l8-8"/>',
    'facebook'  => '<path d="M14 8h2.5V4.5H14c-2.2 0-3.5 1.5-3.5 3.6V10H8v3.5h2.5V21H14v-7.5h2.4l.6-3.5H14V8.4c0-.3.2-.4.5-.4z"/>',
    'linkedin'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="2.5"/><path d="M8 10.5V16M8 7.8v.01M11.5 16v-5.5M11.5 13c0-1.6 1-2.6 2.4-2.6 1.4 0 2.1.9 2.1 2.6V16"/>',
    'youtube'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10 9.5v5l4.5-2.5z"/>',
    'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><path d="M17 7v.01"/>',
    'eye'       => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>',
    'eye-off'   => '<path d="M3 3l18 18"/><path d="M10.6 6A9.6 9.6 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a16 16 0 0 1-3 3.6"/><path d="M6.5 7.6A16 16 0 0 0 2.5 12S6 18.5 12 18.5a9.4 9.4 0 0 0 3.6-.7"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
    'refresh'   => '<path d="M20 11a8 8 0 0 0-14.5-3.5L4 9"/><path d="M4 4v5h5"/><path d="M4 13a8 8 0 0 0 14.5 3.5L20 15"/><path d="M20 20v-5h-5"/>',
    'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
    'play'      => '<path d="M8 5l11 7-11 7z"/>',
    'pause'     => '<path d="M8 5v14M16 5v14"/>',
  );
}

function bdr_v15_sprite() {
  $out = '<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>';
  foreach (bdr_v15_icons() as $id => $paths) {
    $out .= '<symbol id="ic-' . esc_attr($id) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</symbol>';
  }
  return $out . '</defs></svg>';
}

function bdr_v15_icon($name, $class = 'ic') {
  return '<svg class="' . esc_attr($class) . '" aria-hidden="true" focusable="false"><use href="#ic-' . esc_attr($name) . '"/></svg>';
}

/** Icône associée à une page (cartes, menu, bannières). */
function bdr_v15_icon_for($slug) {
  $slug = trim((string)$slug, '/');
  $map = array(
    'la-banque' => 'bank', 'gouvernance' => 'shield', 'engagements' => 'leaf', 'actualites' => 'doc', 'recrutement' => 'users',
    'agences' => 'pin', 'taux-de-change' => 'exchange', 'contact' => 'mail', 'institutionnels' => 'city',
    'particuliers' => 'users', 'comptes-cartes' => 'card', 'comptes-bancaires' => 'bank', 'compte-cheque' => 'bank',
    'operations-quotidiennes' => 'exchange', 'versements-retraits' => 'coins', 'compte-devise' => 'exchange',
    'cartes-bancaires' => 'card', 'carte-cib' => 'card', 'carte-visa' => 'card', 'carte-internationale' => 'globe', 'carte-rahati' => 'card',
    'epargne' => 'coins', 'epargne-disponible' => 'coins', 'epargne-projet' => 'chart', 'epargne-jeune' => 'users', 'livrets-epargne' => 'doc',
    'placements' => 'chart', 'depot-terme' => 'clock', 'bons-caisse' => 'doc',
    'credits' => 'coins', 'credit-immobilier' => 'home', 'credit-consommation' => 'card', 'credit-auto' => 'car',
    'banque-en-ligne' => 'phone', 'services-distance' => 'phone', 'alertes-sms' => 'bell', 'securite-digitale' => 'lock', 'location-coffre' => 'safe',
    'finance-islamique' => 'crescent', 'finance-islamique-comptes' => 'crescent', 'mourabaha' => 'crescent', 'ijara' => 'crescent',
    'mourabaha-consommation' => 'card', 'mourabaha-agriculture' => 'wheat', 'mourabaha-equipements' => 'factory', 'mourabaha-travaux' => 'home',
    'ijara-materiel-roulant' => 'car', 'ijara-medical' => 'shield', 'ijara-travaux-publics' => 'factory',
    'ouvrir-un-compte' => 'user-plus', 'algeriens-residents-etranger' => 'globe',
    'entreprises' => 'briefcase', 'entreprises-comptes-bancaires' => 'briefcase', 'carte-affaires' => 'card', 'placements-entreprises' => 'chart',
    'financement' => 'coins', 'financement-investissement' => 'factory', 'financement-exploitation' => 'exchange', 'financement-pme-pmi' => 'briefcase',
    'commerce-exterieur' => 'ship', 'domiciliation' => 'doc', 'credit-documentaire' => 'doc', 'remise-documentaire' => 'doc', 'transfert-libre' => 'exchange', 'garanties-internationales' => 'shield',
    'agriculture' => 'wheat', 'financement-agriculture' => 'wheat', 'financement-industrie' => 'factory', 'financement-peche-aquaculture' => 'fish',
  );
  return isset($map[$slug]) ? $map[$slug] : 'doc';
}

/* ------------------------------------------------------------------ *
 *  Navigation unique (arbre conforme à l'arborescence validée)
 * ------------------------------------------------------------------ */
function bdr_v15_nav() {
  static $nav = null;
  if ($nav !== null) return $nav;
  $nav = array(
    array('slug' => 'la-banque', 'cols' => array(
      array('head' => 'la-banque', 'items' => array('gouvernance', 'engagements', 'actualites', 'recrutement')),
      array('head' => array('fr' => 'Réseau & informations', 'en' => 'Network & information', 'ar' => 'الشبكة والمعلومات'), 'head_slug' => 'agences',
            'items' => array('agences', 'taux-de-change', 'contact', 'institutionnels')),
    )),
    array('slug' => 'particuliers', 'cols' => array(
      array('head' => 'comptes-cartes', 'items' => array(
        'comptes-bancaires' => array('compte-cheque' => array('operations-quotidiennes' => array('versements-retraits')), 'compte-devise'),
        'cartes-bancaires'  => array('carte-cib', 'carte-visa', 'carte-internationale', 'carte-rahati'),
      )),
      array('head' => 'epargne', 'items' => array('epargne-disponible', 'epargne-projet', 'epargne-jeune', 'livrets-epargne',
        'placements' => array('depot-terme', 'bons-caisse'))),
      array('head' => 'credits', 'items' => array('credit-immobilier', 'credit-consommation', 'credit-auto')),
      array('head' => 'banque-en-ligne', 'items' => array('services-distance', 'alertes-sms', 'securite-digitale', 'location-coffre')),
      array('head' => 'finance-islamique', 'items' => array('finance-islamique-comptes',
        'mourabaha' => array('mourabaha-consommation', 'mourabaha-agriculture', 'mourabaha-equipements', 'mourabaha-travaux'),
        'ijara'     => array('ijara-materiel-roulant', 'ijara-medical', 'ijara-travaux-publics'))),
    ), 'foot' => array('ouvrir-un-compte', 'algeriens-residents-etranger')),
    array('slug' => 'entreprises', 'cols' => array(
      array('head' => array('fr' => 'Comptes & placements', 'en' => 'Accounts & investments', 'ar' => 'الحسابات والاستثمارات'), 'head_slug' => 'entreprises',
            'items' => array('entreprises-comptes-bancaires', 'carte-affaires', 'placements-entreprises')),
      array('head' => 'financement', 'items' => array('financement-investissement', 'financement-exploitation', 'financement-pme-pmi')),
      array('head' => 'commerce-exterieur', 'items' => array('domiciliation', 'credit-documentaire', 'remise-documentaire', 'transfert-libre', 'garanties-internationales')),
      array('head' => 'agriculture', 'items' => array('financement-agriculture', 'financement-industrie', 'financement-peche-aquaculture')),
    )),
    array('slug' => 'finance-islamique', 'cols' => array(
      array('head' => 'finance-islamique', 'items' => array('finance-islamique-comptes', 'livrets-epargne')),
      array('head' => 'mourabaha', 'items' => array('mourabaha-consommation', 'mourabaha-agriculture', 'mourabaha-equipements', 'mourabaha-travaux')),
      array('head' => 'ijara', 'items' => array('ijara-materiel-roulant', 'ijara-medical', 'ijara-travaux-publics')),
    ), 'small' => true),
    array('slug' => 'banque-en-ligne', 'cols' => array(
      array('head' => 'banque-en-ligne', 'items' => array('services-distance', 'alertes-sms', 'securite-digitale', 'location-coffre')),
      array('head' => array('fr' => 'Démarches', 'en' => 'Getting started', 'ar' => 'الإجراءات'), 'head_slug' => 'ouvrir-un-compte',
            'items' => array('ouvrir-un-compte', 'agences', 'contact')),
    ), 'small' => true),
    array('slug' => 'algeriens-residents-etranger', 'cols' => array()),
  );
  return $nav;
}

/** Libellé d'un en-tête de colonne (slug catalogue ou tableau fr/en/ar). */
function bdr_v15_head_label($head, $lang) {
  if (is_array($head)) return isset($head[$lang]) ? $head[$lang] : $head['fr'];
  return bdr_v11_title($head, $lang);
}

/** Rendu récursif d'une liste de liens du méga-menu. */
function bdr_v15_render_items($items, $lang, $depth = 1) {
  echo '<ul class="mega-list depth-' . (int)$depth . '">';
  foreach ($items as $k => $v) {
    if (is_int($k)) { $slug = $v; $kids = array(); } else { $slug = $k; $kids = $v; }
    echo '<li><a href="' . esc_url(bdr_v11_url($slug, $lang)) . '">' . esc_html(bdr_v11_title($slug, $lang)) . '</a>';
    if ($kids) bdr_v15_render_items($kids, $lang, $depth + 1);
    echo '</li>';
  }
  echo '</ul>';
}

/** Tous les slugs de navigation (pour le pied de page, plan du site, contrôles). */
function bdr_v15_nav_slugs() {
  $out = array();
  $walk = function ($items) use (&$walk, &$out) {
    foreach ($items as $k => $v) {
      if (is_int($k)) { $out[] = $v; } else { $out[] = $k; $walk($v); }
    }
  };
  foreach (bdr_v15_nav() as $top) {
    $out[] = $top['slug'];
    foreach ($top['cols'] as $col) {
      if (is_string($col['head'])) $out[] = $col['head'];
      if (!empty($col['head_slug'])) $out[] = $col['head_slug'];
      $walk($col['items']);
    }
    if (!empty($top['foot'])) foreach ($top['foot'] as $s) $out[] = $s;
  }
  return array_values(array_unique($out));
}

/* ------------------------------------------------------------------ *
 *  Bascule de langue fiable (corrige les 404 /fr|en|ar/<slug-article>/)
 * ------------------------------------------------------------------ */
function bdr_v15_switch_url($target_lang) {
  if (is_singular('bdr_actualite') || is_post_type_archive('bdr_actualite')) return bdr_v11_url('actualites', $target_lang);
  if (is_singular('bdr_agence') || is_post_type_archive('bdr_agence')) return bdr_v11_url('agences', $target_lang);
  if (is_404() || is_search()) return bdr_v11_url('', $target_lang);
  $slug = function_exists('bdr_v11_get_current_slug') ? bdr_v11_get_current_slug() : '';
  $cat = bdr_v11_catalog();
  if ($slug !== '' && !isset($cat[$slug])) return bdr_v11_url('', $target_lang);
  return bdr_v11_url($slug, $target_lang);
}

/* ------------------------------------------------------------------ *
 *  Images par page : photos HD (assets/images/photos) affectées à chaque page.
 *  Chaque photo existe en 4 cadrages (plein, gauche, droite, centre) : deux pages qui partagent
 *  une photo n'affichent donc pas le même plan. Pour une photo propre à une page, déposer
 *  assets/images/pages/<slug>.webp (1600×900) : elle remplace la photo sans toucher au code.
 * ------------------------------------------------------------------ */
function bdr_v15_photo_map() {
  return array(
    'la-banque' => 'headquarters', 'gouvernance' => 'business-consult', 'engagements' => 'algiers-coast', 'actualites' => 'algiers-coast',
    'recrutement' => 'business-consult', 'agences' => 'headquarters', 'taux-de-change' => 'commerce', 'contact' => 'customer-service', 'institutionnels' => 'headquarters',
    'particuliers' => 'family', 'comptes-cartes' => 'customer-service', 'comptes-bancaires' => 'customer-service', 'compte-cheque' => 'customer-service',
    'operations-quotidiennes' => 'digital-banking', 'versements-retraits' => 'customer-service', 'compte-devise' => 'commerce',
    'cartes-bancaires' => 'digital-banking', 'carte-cib' => 'digital-banking', 'carte-visa' => 'commerce', 'carte-rahati' => 'family', 'carte-internationale' => 'algiers-coast',
    'epargne' => 'savings', 'epargne-disponible' => 'savings', 'epargne-projet' => 'family', 'epargne-jeune' => 'family', 'livrets-epargne' => 'savings',
    'placements' => 'savings', 'depot-terme' => 'savings', 'bons-caisse' => 'savings',
    'credits' => 'family', 'credit-immobilier' => 'family', 'credit-consommation' => 'customer-service', 'credit-auto' => 'algiers-coast',
    'banque-en-ligne' => 'digital-banking', 'bdr-net' => 'digital-banking', 'services-distance' => 'digital-banking', 'alertes-sms' => 'digital-banking', 'securite-digitale' => 'digital-banking', 'location-coffre' => 'headquarters',
    'finance-islamique' => 'savings', 'finance-islamique-comptes' => 'customer-service', 'mourabaha' => 'family', 'mourabaha-consommation' => 'family',
    'mourabaha-travaux' => 'family', 'mourabaha-equipements' => 'business-consult', 'mourabaha-agriculture' => 'agriculture',
    'ijara' => 'business-consult', 'ijara-materiel-roulant' => 'commerce', 'ijara-medical' => 'business-consult', 'ijara-travaux-publics' => 'commerce',
    'ouvrir-un-compte' => 'customer-service', 'algeriens-residents-etranger' => 'algiers-coast',
    'entreprises' => 'business-consult', 'entreprises-comptes-bancaires' => 'business-consult', 'carte-affaires' => 'business-consult', 'placements-entreprises' => 'savings',
    'financement' => 'commerce', 'financement-investissement' => 'commerce', 'financement-exploitation' => 'business-consult', 'financement-pme-pmi' => 'business-consult',
    'commerce-exterieur' => 'commerce', 'domiciliation' => 'commerce', 'credit-documentaire' => 'commerce', 'remise-documentaire' => 'commerce',
    'transfert-libre' => 'commerce', 'garanties-internationales' => 'commerce',
    'agriculture' => 'agriculture', 'financement-agriculture' => 'agriculture', 'financement-industrie' => 'commerce', 'financement-peche-aquaculture' => 'algiers-coast',
    'mentions-legales' => 'headquarters', 'donnees-personnelles' => 'headquarters', 'plan-du-site' => 'headquarters',
  );
}

/** Textes alternatifs descriptifs, par photo (par langue). */
function bdr_v16_photo_alt($name, $lang) {
  $a = array(
    'headquarters' => array('Siège de la BDR : façade vitrée et drapeau algérien', 'BDR head office: glass façade and the Algerian flag', 'مقر بنك BDR: واجهة زجاجية والعلم الجزائري'),
    'algiers-coast' => array('Baie d’Alger et front de mer', 'Bay of Algiers and waterfront', 'خليج الجزائر وواجهتها البحرية'),
    'business-consult' => array('Conseillers BDR accueillant un couple en agence', 'BDR advisers welcoming a couple in a branch', 'مستشارو BDR يستقبلون زوجين في الوكالة'),
    'commerce' => array('Port de commerce, conteneurs et grues : commerce extérieur', 'Trade port with containers and cranes: foreign trade', 'ميناء تجاري وحاويات ورافعات: التجارة الخارجية'),
    'customer-service' => array('Conseillère clientèle BDR recevant un client au guichet', 'BDR customer adviser serving a client at the desk', 'مستشارة زبائن BDR تستقبل زبوناً عند الشباك'),
    'digital-banking' => array('Banque en ligne : interface numérique sur ordinateur portable', 'Online banking: digital interface on a laptop', 'الخدمات المصرفية الرقمية على حاسوب محمول'),
    'family' => array('Famille algérienne consultant une offre bancaire sur ordinateur', 'Algerian family reviewing a banking offer on a laptop', 'عائلة جزائرية تطّلع على عرض مصرفي عبر الحاسوب'),
    'savings' => array('Pièces empilées et jeune pousse : épargne et placements', 'Stacked coins and a young plant: savings and investments', 'عملات مكدسة ونبتة صغيرة: الادخار والاستثمار'),
    'agriculture' => array('Agriculteur consultant une tablette devant un champ irrigué', 'Farmer checking a tablet in front of an irrigated field', 'فلاح يتصفح لوحاً رقمياً أمام حقل مسقي'),
  );
  $i = $lang === 'ar' ? 2 : ($lang === 'en' ? 1 : 0);
  return isset($a[$name]) ? $a[$name][$i] : $a['headquarters'][$i];
}

/** Retourne array('src','src800','w','h','custom','photo','alt'[lang]). */
function bdr_v15_image($slug) {
  static $cache = array(), $variants = null;
  $slug = sanitize_title($slug);
  if (isset($cache[$slug])) return $cache[$slug];
  $dir = get_template_directory() . '/assets/images/';
  $uri = get_template_directory_uri() . '/assets/images/';
  $map = bdr_v15_photo_map();
  if ($variants === null) {
    $variants = array(); $count = array(); $suffix = array('', '-l', '-r', '-c');
    foreach ($map as $k => $ph) { $n = isset($count[$ph]) ? $count[$ph] : 0; $variants[$k] = $suffix[$n % 4]; $count[$ph] = $n + 1; }
  }
  $name = isset($map[$slug]) ? $map[$slug] : 'headquarters';
  foreach (array('webp', 'jpg', 'jpeg', 'png') as $ext) {
    if ($slug !== '' && file_exists($dir . 'pages/' . $slug . '.' . $ext)) {
      $small = file_exists($dir . 'pages/' . $slug . '-800.' . $ext) ? $uri . 'pages/' . $slug . '-800.' . $ext : $uri . 'pages/' . $slug . '.' . $ext;
      return $cache[$slug] = array('src' => $uri . 'pages/' . $slug . '.' . $ext, 'src800' => $small, 'w' => 1600, 'h' => 900, 'custom' => true, 'photo' => $name);
    }
  }
  $v = isset($variants[$slug]) ? $variants[$slug] : '';
  return $cache[$slug] = array('src' => $uri . 'photos/' . $name . $v . '.webp', 'src800' => $uri . 'photos/' . $name . $v . '-800.webp', 'w' => 1600, 'h' => 900, 'custom' => false, 'photo' => $name);
}

/** Galerie panoramique de l'accueil (6 bandes 3,2:1 tirées des photos HD). */
function bdr_v16_panoramas($lang) {
  $uri = get_template_directory_uri() . '/assets/images/panoramas/';
  $defs = array(
    array('pano-alger', array('La baie d’Alger', 'The Bay of Algiers', 'خليج الجزائر'), array('Une banque présente dans les grandes villes du littoral, de la baie aux quartiers du centre.', 'A bank present in the big coastal cities, from the bay to the city centres.', 'بنك حاضر في كبريات مدن الساحل، من الخليج إلى وسط المدينة.')),
    array('pano-siege', array('Le siège et les agences', 'Head office and branches', 'المقر والوكالات'), array('Un siège moderne et un réseau d’agences au plus près de vous.', 'A modern head office and a branch network close to you.', 'مقر عصري وشبكة وكالات قريبة منك.')),
    array('pano-port', array('Les ports et le commerce extérieur', 'Ports and foreign trade', 'الموانئ والتجارة الخارجية'), array('Domiciliation, crédit documentaire et garanties pour les opérateurs du commerce extérieur.', 'Domiciliation, documentary credit and guarantees for foreign-trade operators.', 'التوطين والاعتماد المستندي والضمانات لمتعاملي التجارة الخارجية.')),
    array('pano-plaines', array('Les campagnes et l’agriculture', 'Farming and the countryside', 'الفلاحة والأرياف'), array('Des financements adaptés aux exploitants : équipement, campagne, irrigation.', 'Financing tailored to farmers: equipment, seasonal needs, irrigation.', 'تمويلات ملائمة للفلاحين: التجهيز والحملة الفلاحية والسقي.')),
    array('pano-conseil', array('Un conseiller pour chaque projet', 'An adviser for every project', 'مستشار لكل مشروع'), array('Particuliers, professionnels et entreprises : on vous accompagne en agence.', 'Individuals, professionals and companies: we support you in branch.', 'أفراد ومهنيون ومؤسسات: نرافقكم في الوكالة.')),
    array('pano-epargne', array('Épargner pour l’avenir', 'Saving for the future', 'الادخار للمستقبل'), array('Livrets, dépôts à terme et placements pour faire grandir votre épargne.', 'Savings books, term deposits and investments to grow your savings.', 'دفاتر ادخار وودائع لأجل وتوظيفات لتنمية مدخراتكم.')),
  );
  $i = $lang === 'ar' ? 2 : ($lang === 'en' ? 1 : 0); $out = array();
  foreach ($defs as $d) $out[] = array('src' => $uri . $d[0] . '.webp', 'src_s' => $uri . $d[0] . '-800.webp', 'title' => $d[1][$i], 'text' => $d[2][$i]);
  return $out;
}

/* ------------------------------------------------------------------ *
 *  Taux de change (option unique, éditable dans l'administration)
 * ------------------------------------------------------------------ */
function bdr_v15_fx_defaults() {
  return array(
    array('code' => 'EUR', 'value' => '153,2126'), array('code' => 'USD', 'value' => '133,6467'),
    array('code' => 'GBP', 'value' => '178,7318'), array('code' => 'CHF', 'value' => '162,9242'),
    array('code' => 'CAD', 'value' => '95,2884'),  array('code' => 'CNY', 'value' => '19,9509'),
    array('code' => 'AED', 'value' => '36,3902'),  array('code' => 'SAR', 'value' => '35,5841'),
    array('code' => 'TND', 'value' => '45,3963'),  array('code' => 'MAD', 'value' => '14,0313'),
  );
}
function bdr_v15_fx_rows() {
  $set = function_exists('bdr_v16_fx_dataset') ? bdr_v16_fx_dataset() : array('rows' => bdr_v15_fx_defaults());
  $rows = $set['rows'];
  $cat = function_exists('bdr_v16_fx_catalog') ? bdr_v16_fx_catalog() : array();
  foreach ($rows as $i => $r) {
    $rows[$i]['names'] = isset($cat[$r['code']]) ? array('fr' => $cat[$r['code']]['fr'], 'en' => $cat[$r['code']]['en'], 'ar' => $cat[$r['code']]['ar']) : array('fr' => $r['code'], 'en' => $r['code'], 'ar' => $r['code']);
    if (!isset($rows[$i]['buy'])) $rows[$i]['buy'] = '';
    if (!isset($rows[$i]['sell'])) $rows[$i]['sell'] = '';
    // Cours publié pour 100 unités (yen) : « Yen (100) ».
    if (!empty($r['per']) && (int)$r['per'] > 1) foreach ($rows[$i]['names'] as $l => $n) $rows[$i]['names'][$l] = $n . ' (' . (int)$r['per'] . ')';
  }
  return $rows;
}
function bdr_v15_fx_date($lang) {
  $set = function_exists('bdr_v16_fx_dataset') ? bdr_v16_fx_dataset() : array('date' => bdr_v15_opt('fx_date'));
  $ts = strtotime($set['date']);
  if (!$ts) return '';
  $months = array(
    'fr' => array('', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'),
    'en' => array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
    'ar' => array('', 'جانفي', 'فيفري', 'مارس', 'أفريل', 'ماي', 'جوان', 'جويلية', 'أوت', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'),
  );
  $m = $months[$lang][(int)date('n', $ts)];
  return $lang === 'en' ? $m . ' ' . date('j, Y', $ts) : date('j', $ts) . ' ' . $m . ' ' . date('Y', $ts);
}

/* ------------------------------------------------------------------ *
 *  Statistiques réseau réelles (calculées sur la base des agences)
 * ------------------------------------------------------------------ */
function bdr_v15_network_stats() {
  $cached = get_transient('bdr_v15_network_stats');
  if (is_array($cached)) return $cached;
  $ids = get_posts(array('post_type' => 'bdr_agence', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true));
  $w = array();
  foreach ($ids as $id) { $v = get_post_meta($id, '_bdr_wilaya', true); if ($v) $w[$v] = 1; }
  $stats = array('agencies' => count($ids), 'wilayas' => count($w));
  set_transient('bdr_v15_network_stats', $stats, DAY_IN_SECONDS);
  return $stats;
}
function bdr_v15_flush_stats() { delete_transient('bdr_v15_network_stats'); }
add_action('save_post_bdr_agence', 'bdr_v15_flush_stats');
add_action('deleted_post', 'bdr_v15_flush_stats');

/* ------------------------------------------------------------------ *
 *  Petits utilitaires d'affichage
 * ------------------------------------------------------------------ */
function bdr_v15_t($fr, $en, $ar, $lang = null) {
  $lang = $lang ?: bdr_v11_lang();
  return $lang === 'ar' ? $ar : ($lang === 'en' ? $en : $fr);
}

/** Balise <img> avec dimensions (évite le CLS), lazy/eager et srcset. */
function bdr_v15_img_tag($img, $alt, $eager = false, $sizes = '100vw', $class = '') {
  $img = array_merge(array('w' => 1600, 'h' => 900), $img);
  return '<img' . ($class ? ' class="' . esc_attr($class) . '"' : '') . ' src="' . esc_url($img['src']) . '" srcset="' . esc_url($img['src800']) . ' 800w, ' . esc_url($img['src']) . ' 1600w" sizes="' . esc_attr($sizes) . '" width="' . (int)$img['w'] . '" height="' . (int)$img['h'] . '" alt="' . esc_attr($alt) . '" decoding="async"' . ($eager ? ' fetchpriority="high"' : ' loading="lazy"') . '>';
}
