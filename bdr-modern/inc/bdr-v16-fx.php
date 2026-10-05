<?php
/**
 * BDR V16 — cours de change : récupération quotidienne automatique.
 *
 * Source : page « Taux de change journalier » de la Banque d'Algérie.
 *  - Une tâche WP-Cron quotidienne télécharge la page (une seule requête par jour, User-Agent explicite).
 *  - Un analyseur tolérant lit le premier tableau qui contient des devises et des montants
 *    (codes ISO ou noms FR/AR, colonnes achat/vente ou cours unique, virgule ou point décimal).
 *  - En cas d'échec (site indisponible, page modifiée, contenu chargé par JavaScript…), les derniers cours
 *    valides restent affichés avec leur date ; l'état est visible dans Administration › Taux de change.
 *  - Saisie manuelle toujours possible (mode « manuel » ou cours de secours).
 */
if (!defined('ABSPATH')) exit;

define('BDR_FX_HOOK', 'bdr_v16_fx_daily');

function bdr_v16_fx_source() {
  $u = get_option('bdr_fx_source', '');
  if (!is_string($u) || !preg_match('#^https://#i', $u)) $u = 'https://www.bank-of-algeria.dz/taux-de-change-journalier/';
  return $u;
}

/* ------------------------------------------------------------------ *
 *  Référentiel devises (codes ISO, noms FR/EN/AR et alias de reconnaissance)
 * ------------------------------------------------------------------ */
function bdr_v16_fx_catalog() {
  return array(
    'EUR' => array('fr' => 'Euro', 'en' => 'Euro', 'ar' => 'يورو', 'alias' => array('euro', 'يورو')),
    'USD' => array('fr' => 'Dollar US', 'en' => 'US dollar', 'ar' => 'دولار أمريكي', 'alias' => array('dollar us', 'dollar americain', 'dollar des etats-unis', 'dollar des etats unis', 'us dollar', 'دولار امريكي', 'دولار أمريكي', 'الدولار الامريكي', 'الدولار الأمريكي')),
    'GBP' => array('fr' => 'Livre sterling', 'en' => 'Pound sterling', 'ar' => 'جنيه إسترليني', 'alias' => array('livre sterling', 'livre britannique', 'pound sterling', 'جنيه استرليني', 'جنيه إسترليني', 'الجنيه الاسترليني', 'الجنيه الإسترليني')),
    'CHF' => array('fr' => 'Franc suisse', 'en' => 'Swiss franc', 'ar' => 'فرنك سويسري', 'alias' => array('franc suisse', 'swiss franc', 'فرنك سويسري', 'الفرنك السويسري')),
    'CAD' => array('fr' => 'Dollar canadien', 'en' => 'Canadian dollar', 'ar' => 'دولار كندي', 'alias' => array('dollar canadien', 'canadian dollar', 'دولار كندي', 'الدولار الكندي')),
    'CNY' => array('fr' => 'Yuan', 'en' => 'Yuan', 'ar' => 'يوان', 'alias' => array('yuan', 'renminbi', 'يوان', 'اليوان الصيني', 'اليوان')),
    'JPY' => array('fr' => 'Yen', 'en' => 'Japanese yen', 'ar' => 'ين ياباني', 'alias' => array('yen', 'yens', 'yen japonais', 'yens japonais', 'ين ياباني', 'الين الياباني', 'ين')),
    'AED' => array('fr' => 'Dirham des Émirats', 'en' => 'UAE dirham', 'ar' => 'درهم إماراتي', 'alias' => array('dirham des emirats', 'dirham emirati', 'uae dirham', 'درهم اماراتي', 'درهم إماراتي', 'الدرهم الاماراتي', 'الدرهم الإماراتي')),
    'SAR' => array('fr' => 'Riyal saoudien', 'en' => 'Saudi riyal', 'ar' => 'ريال سعودي', 'alias' => array('riyal saoudien', 'saudi riyal', 'ريال سعودي', 'الريال السعودي')),
    'TND' => array('fr' => 'Dinar tunisien', 'en' => 'Tunisian dinar', 'ar' => 'دينار تونسي', 'alias' => array('dinar tunisien', 'tunisian dinar', 'دينار تونسي', 'الدينار التونسي')),
    'MAD' => array('fr' => 'Dirham marocain', 'en' => 'Moroccan dirham', 'ar' => 'درهم مغربي', 'alias' => array('dirham marocain', 'moroccan dirham', 'درهم مغربي', 'الدرهم المغربي')),
    'EGP' => array('fr' => 'Livre égyptienne', 'en' => 'Egyptian pound', 'ar' => 'جنيه مصري', 'alias' => array('livre egyptienne', 'egyptian pound', 'جنيه مصري', 'الجنيه المصري')),
    'KWD' => array('fr' => 'Dinar koweïtien', 'en' => 'Kuwaiti dinar', 'ar' => 'دينار كويتي', 'alias' => array('dinar koweitien', 'kuwaiti dinar', 'دينار كويتي', 'الدينار الكويتي')),
    'QAR' => array('fr' => 'Riyal qatari', 'en' => 'Qatari riyal', 'ar' => 'ريال قطري', 'alias' => array('riyal qatari', 'qatari riyal', 'ريال قطري', 'الريال القطري')),
    'LYD' => array('fr' => 'Dinar libyen', 'en' => 'Libyan dinar', 'ar' => 'دينار ليبي', 'alias' => array('dinar libyen', 'libyan dinar', 'دينار ليبي', 'الدينار الليبي')),
    'TRY' => array('fr' => 'Livre turque', 'en' => 'Turkish lira', 'ar' => 'ليرة تركية', 'alias' => array('livre turque', 'turkish lira', 'ليرة تركية', 'الليرة التركية')),
    'RUB' => array('fr' => 'Rouble russe', 'en' => 'Russian rouble', 'ar' => 'روبل روسي', 'alias' => array('rouble', 'rouble russe', 'روبل روسي', 'الروبل الروسي')),
    'SEK' => array('fr' => 'Couronne suédoise', 'en' => 'Swedish krona', 'ar' => 'كرونة سويدية', 'alias' => array('couronne suedoise', 'كرونة سويدية', 'الكرونة السويدية')),
    'NOK' => array('fr' => 'Couronne norvégienne', 'en' => 'Norwegian krone', 'ar' => 'كرونة نرويجية', 'alias' => array('couronne norvegienne', 'كرونة نرويجية', 'الكرونة النرويجية')),
    'DKK' => array('fr' => 'Couronne danoise', 'en' => 'Danish krone', 'ar' => 'كرونة دنماركية', 'alias' => array('couronne danoise', 'كرونة دنماركية', 'الكرونة الدنماركية')),
    'AUD' => array('fr' => 'Dollar australien', 'en' => 'Australian dollar', 'ar' => 'دولار أسترالي', 'alias' => array('dollar australien', 'دولار استرالي', 'دولار أسترالي', 'الدولار الاسترالي')),
    'INR' => array('fr' => 'Roupie indienne', 'en' => 'Indian rupee', 'ar' => 'روبية هندية', 'alias' => array('roupie indienne', 'روبية هندية', 'الروبية الهندية')),
    'BHD' => array('fr' => 'Dinar bahreïni', 'en' => 'Bahraini dinar', 'ar' => 'دينار بحريني', 'alias' => array('dinar bahreini', 'دينار بحريني', 'الدينار البحريني')),
    'OMR' => array('fr' => 'Rial omanais', 'en' => 'Omani rial', 'ar' => 'ريال عماني', 'alias' => array('rial omanais', 'ريال عماني', 'الريال العماني')),
    'JOD' => array('fr' => 'Dinar jordanien', 'en' => 'Jordanian dinar', 'ar' => 'دينار أردني', 'alias' => array('dinar jordanien', 'دينار اردني', 'دينار أردني', 'الدينار الاردني', 'الدينار الأردني')),
  );
}
/** Ordre d'affichage : devises courantes d'abord. */
function bdr_v16_fx_priority() { return array('EUR', 'USD', 'GBP', 'CHF', 'CAD', 'CNY', 'AED', 'SAR', 'TND', 'MAD', 'JPY', 'EGP', 'QAR', 'KWD', 'TRY', 'RUB'); }

function bdr_v16_fx_norm($s) {
  $s = html_entity_decode((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  $s = preg_replace('/[\x{00A0}\x{200B}-\x{200F}\x{202A}-\x{202E}\s]+/u', ' ', $s);
  $s = trim($s);
  if (function_exists('mb_strtolower')) $s = mb_strtolower($s, 'UTF-8'); else $s = strtolower($s);
  $s = strtr($s, array('é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ù' => 'u', 'û' => 'u', 'ç' => 'c', '’' => "'", 'ـ' => ''));
  return $s;
}

/** Devise reconnue dans un texte de cellule (code ISO seul, ou nom connu). Retourne le code ou ''. */
function bdr_v16_fx_detect($text) {
  $cat = bdr_v16_fx_catalog();
  $raw = trim((string)$text);
  if (preg_match('/\b([A-Z]{3})\b/', $raw, $m) && isset($cat[$m[1]])) return $m[1];
  $strip = function ($x) { return preg_replace('/(^|\s)ال(?=\S)/u', '$1', $x); }; // article arabe « ال »
  $n = $strip(bdr_v16_fx_norm($raw));
  if ($n === '') return '';
  // D'abord correspondance exacte, puis alias en mots entiers (évite « ين » dans « دينار »), le plus long d'abord.
  $cands = array();
  foreach ($cat as $code => $c) foreach ($c['alias'] as $a) { $a = $strip(bdr_v16_fx_norm($a)); if ($a !== '') $cands[] = array($a, $code); }
  foreach ($cands as $c) if ($n === $c[0]) return $c[1];
  usort($cands, function ($x, $y) { return strlen($y[0]) - strlen($x[0]); });
  foreach ($cands as $c) if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote($c[0], '/') . '(?![\p{L}\p{N}])/u', $n)) return $c[1];
  return '';
}

/** Nombre décimal depuis « 153,2126 », « 153.2126 », « 1 234,56 », « 1,234.56 ». null si ce n'est pas un nombre. */
function bdr_v16_fx_num($text) {
  $s = trim(preg_replace('/[\x{00A0}\x{202F}\s]+/u', '', (string)$text));
  $s = preg_replace('/(DA|DZD|دج)$/iu', '', $s);
  if ($s === '' || !preg_match('/^\d[\d.,]*$/', $s)) return null;
  $c = strrpos($s, ','); $p = strrpos($s, '.');
  if ($c !== false && $p !== false) { if ($c > $p) $s = str_replace('.', '', $s); else $s = str_replace(',', '', $s); $s = str_replace(',', '.', $s); }
  elseif ($c !== false) { $s = (substr_count($s, ',') > 1) ? str_replace(',', '', $s) : str_replace(',', '.', $s); }
  elseif ($p !== false && substr_count($s, '.') > 1) { $s = str_replace('.', '', $s); }
  if (!is_numeric($s)) return null;
  return (float)$s;
}
function bdr_v16_fx_fmt($f) { return number_format((float)$f, 4, ',', ' '); }

/** Date (AAAA-MM-JJ) trouvée dans un texte : 22/09/2026, 22-09-2026, 2026-09-22, « 22 septembre 2026 », « 22 سبتمبر 2026 ». */
function bdr_v16_fx_find_date($text) {
  $t = bdr_v16_fx_norm($text);
  $t = strtr($t, array('٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'));
  $ok = function ($y, $m, $d) { return $y >= 2000 && $y <= 2100 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31 && checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : ''; };
  if (preg_match('#\b(\d{1,2})\s*[/.\-]\s*(\d{1,2})\s*[/.\-]\s*(20\d{2})\b#', $t, $m) && ($r = $ok((int)$m[3], (int)$m[2], (int)$m[1]))) return $r;
  if (preg_match('#\b(20\d{2})\s*[/.\-]\s*(\d{1,2})\s*[/.\-]\s*(\d{1,2})\b#', $t, $m) && ($r = $ok((int)$m[1], (int)$m[2], (int)$m[3]))) return $r;
  $months = array('janvier' => 1, 'fevrier' => 2, 'mars' => 3, 'avril' => 4, 'mai' => 5, 'juin' => 6, 'juillet' => 7, 'aout' => 8, 'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'decembre' => 12,
    'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
    'جانفي' => 1, 'يناير' => 1, 'فيفري' => 2, 'فبراير' => 2, 'مارس' => 3, 'افريل' => 4, 'أفريل' => 4, 'ابريل' => 4, 'أبريل' => 4, 'ماي' => 5, 'مايو' => 5, 'جوان' => 6, 'يونيو' => 6, 'جويلية' => 7, 'يوليو' => 7, 'اوت' => 8, 'أوت' => 8, 'أغسطس' => 8, 'اغسطس' => 8, 'سبتمبر' => 9, 'اكتوبر' => 10, 'أكتوبر' => 10, 'نوفمبر' => 11, 'ديسمبر' => 12);
  foreach ($months as $name => $num) {
    if (preg_match('#\b(\d{1,2})\s*(?:er)?\s+' . preg_quote($name, '#') . '\s+(20\d{2})#u', $t, $m) && ($r = $ok((int)$m[2], $num, (int)$m[1]))) return $r;
    if (preg_match('#' . preg_quote($name, '#') . '\s+(\d{1,2}),?\s+(20\d{2})#u', $t, $m) && ($r = $ok((int)$m[2], $num, (int)$m[1]))) return $r;
  }
  return '';
}

/**
 * Analyse le HTML et retourne array('rows' => array(array(code, buy, sell, value)), 'date' => 'AAAA-MM-JJ'|'', 'error' => '').
 * Méthode : pour chaque <table>, on repère (a) les colonnes d'en-tête achat/vente/cours si elles existent,
 * (b) pour chaque ligne, la devise puis les nombres plausibles ; sinon les deux premiers nombres = achat, vente.
 */
function bdr_v16_fx_parse($html) {
  $out = array('rows' => array(), 'date' => '', 'error' => '');
  if (!is_string($html) || strlen($html) < 200) { $out['error'] = 'empty'; return $out; }
  if (!class_exists('DOMDocument')) { $out['error'] = 'no_dom'; return $out; }
  $prev = libxml_use_internal_errors(true);
  $dom = new DOMDocument();
  $enc = '<?xml encoding="UTF-8">';
  $dom->loadHTML($enc . $html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET);
  libxml_clear_errors(); libxml_use_internal_errors($prev);
  $xp = new DOMXPath($dom);
  foreach ($xp->query('//script|//style|//noscript') as $n) $n->parentNode->removeChild($n);
  $page_text = $dom->documentElement ? $dom->documentElement->textContent : '';
  $best = array(); $best_table = null; $best_day = '';
  foreach ($xp->query('//table') as $table) {
    $rows = array(); $cols = array('buy' => -1, 'sell' => -1, 'value' => -1, 'unit' => -1);
    $day_col = -1; $day_date = ''; // présentation « une colonne par jour » : colonne et date les plus récentes
    foreach ($xp->query('.//tr', $table) as $tr) {
      $cells = array();
      foreach ($xp->query('./th|./td', $tr) as $c) $cells[] = trim(preg_replace('/\s+/u', ' ', $c->textContent));
      if (count($cells) < 2) continue;
      // ligne d'en-tête ?
      $hdr = 0; $tmp = array('buy' => -1, 'sell' => -1, 'value' => -1, 'unit' => -1);
      foreach ($cells as $i => $cell) {
        $n = bdr_v16_fx_norm($cell);
        if (preg_match('/^(achat|buy|buying|شراء|الشراء)\b/u', $n)) { $tmp['buy'] = $i; $hdr++; }
        elseif (preg_match('/^(vente|sell|selling|sale|بيع|البيع)\b/u', $n)) { $tmp['sell'] = $i; $hdr++; }
        elseif (preg_match('/^(cours|taux|rate|value|السعر|سعر)/u', $n)) { $tmp['value'] = $i; $hdr++; }
        elseif (preg_match('/^(unite|unit|nominal|وحدة|الوحدة)/u', $n)) { $tmp['unit'] = $i; $hdr++; }
      }
      if ($hdr >= 1 && bdr_v16_fx_num(end($cells)) === null && !preg_match('/\d/', implode('', $cells))) { $cols = $tmp; continue; }
      // En-tête de dates (Banque d'Algérie : « | 05-10-2026 | 02-10-2026 | 01-10-2026 | … ») : on retient le jour le plus récent.
      $dates = array();
      foreach ($cells as $i => $cell) { if (bdr_v16_fx_num($cell) === null && ($d = bdr_v16_fx_find_date($cell)) !== '') $dates[$i] = $d; }
      if (count($dates) >= 2) { arsort($dates); $day_col = key($dates); $day_date = current($dates); continue; }
      $code = ''; $name_unit = 1.0;
      foreach ($cells as $cell) {
        if (bdr_v16_fx_num($cell) !== null) continue;
        $code = bdr_v16_fx_detect($cell);
        if ($code) {
          // Devise cotée par 10, 100 ou 1000 unités indiquée dans son libellé : « Yen (100) », « 100 Yens », « JPY x100 ».
          if (preg_match('/\(\s*(10|100|1000)\s*\)|^\s*(10|100|1000)\s+\D|[x×]\s*(10|100|1000)\b/u', $cell, $um)) $name_unit = (float)max(array_slice($um, 1));
          break;
        }
      }
      if (!$code) continue;
      $nums = array(); // index de cellule => valeur
      foreach ($cells as $i => $cell) { $v = bdr_v16_fx_num($cell); if ($v !== null && $v > 0.0001 && $v < 1000000) $nums[$i] = $v; }
      if (!$nums) continue;
      $buy = $sell = $val = null; $unit = $name_unit;
      if ($cols['buy'] >= 0 && isset($nums[$cols['buy']])) $buy = $nums[$cols['buy']];
      if ($cols['sell'] >= 0 && isset($nums[$cols['sell']])) $sell = $nums[$cols['sell']];
      if ($cols['value'] >= 0 && isset($nums[$cols['value']])) $val = $nums[$cols['value']];
      if ($cols['unit'] >= 0 && isset($nums[$cols['unit']])) $unit = $nums[$cols['unit']];
      if ($day_col >= 0) { if (!isset($nums[$day_col])) continue; $val = $nums[$day_col]; } // cours du jour le plus récent uniquement
      if ($buy === null && $sell === null && $val === null) {
        $vals = array_values($nums);
        // On écarte une éventuelle colonne « unité » (1, 10, 100…) placée avant les cours.
        if (count($vals) >= 3 && in_array($vals[0], array(1.0, 10.0, 100.0, 1000.0), true)) { $unit = $vals[0]; array_shift($vals); }
        if (count($vals) >= 2) { $buy = $vals[0]; $sell = $vals[1]; } else { $val = $vals[0]; }
      }
      // Cours par 100 (ou 10, 1000) unités → ramenés à 1 unité pour un affichage homogène.
      if ($unit > 1) { foreach (array('buy', 'sell', 'val') as $k) if ($$k !== null) $$k = $$k / $unit; }
      $rows[$code] = array('code' => $code, 'buy' => $buy, 'sell' => $sell, 'value' => $val !== null ? $val : ($sell !== null ? $sell : $buy));
      // Yen publié pour 100 unités (≈ 85 DA) : la valeur publiée est conservée, le libellé l'indiquera.
      if ($code === 'JPY' && $rows[$code]['value'] !== null && $rows[$code]['value'] >= 5) $rows[$code]['per'] = 100;
    }
    if (count($rows) > count($best)) { $best = $rows; $best_table = $table; $best_day = $day_date; }
  }
  if (count($best) < 2) { $out['error'] = 'no_table'; return $out; }
  // Cours plausibles : 1 devise = entre 0,01 et 100 000 DZD (filtre les colonnes qui ne sont pas des cours).
  foreach ($best as $code => $r) if ($r['value'] === null || $r['value'] < 0.01 || $r['value'] > 100000) unset($best[$code]);
  if (count($best) < 2) { $out['error'] = 'implausible'; return $out; }
  $prio = array_flip(bdr_v16_fx_priority());
  uasort($best, function ($a, $b) use ($prio) {
    $pa = isset($prio[$a['code']]) ? $prio[$a['code']] : 100; $pb = isset($prio[$b['code']]) ? $prio[$b['code']] : 100;
    return $pa === $pb ? strcmp($a['code'], $b['code']) : $pa - $pb;
  });
  $rows = array();
  foreach (array_slice(array_values($best), 0, 24) as $r) {
    $rows[] = array('code' => $r['code'], 'per' => isset($r['per']) ? (int)$r['per'] : 1,
      'buy' => $r['buy'] !== null ? bdr_v16_fx_fmt($r['buy']) : '', 'sell' => $r['sell'] !== null ? bdr_v16_fx_fmt($r['sell']) : '',
      'value' => bdr_v16_fx_fmt($r['value']));
  }
  $out['rows'] = $rows;
  // Date des cours : d'abord dans le tableau retenu et les éléments qui le précèdent (titre « Cours du … »),
  // puis seulement dans le reste de la page (qui peut contenir d'autres dates : actualités, pied de page…).
  $near = '';
  if ($best_table) {
    $near = $best_table->textContent;
    foreach (array($best_table, $best_table->parentNode) as $node) {
      $sib = $node ? $node->previousSibling : null;
      for ($i = 0; $sib && $i < 6; $sib = $sib->previousSibling) {
        if ($sib->nodeType !== XML_ELEMENT_NODE) continue;
        $near = $sib->textContent . ' ' . $near; $i++;
      }
    }
  }
  $out['date'] = $best_day !== '' ? $best_day : bdr_v16_fx_find_date(mb_substr($near, 0, 8000, 'UTF-8'));
  if ($out['date'] === '') $out['date'] = bdr_v16_fx_find_date(mb_substr($page_text, 0, 20000, 'UTF-8'));
  return $out;
}

/* ------------------------------------------------------------------ *
 *  Téléchargement + enregistrement
 * ------------------------------------------------------------------ */
function bdr_v16_fx_set_status($ok, $message, $count = 0) {
  $old = get_option('bdr_fx_status', array());
  $st = array('ok' => (bool)$ok, 'time' => time(), 'message' => (string)$message, 'count' => (int)$count,
    'last_ok' => $ok ? time() : (isset($old['last_ok']) ? $old['last_ok'] : 0));
  update_option('bdr_fx_status', $st);
  return $st;
}

/* ------------------------------------------------------------------ *
 *  Chaîne de certificats incomplète chez la source (cURL error 60)
 *
 *  Le serveur de la Banque d'Algérie n'envoie pas son certificat intermédiaire. Les navigateurs le
 *  téléchargent eux-mêmes (adresse « CA Issuers » inscrite dans le certificat) ; on fait de même.
 *  La vérification TLS reste complète : l'intermédiaire téléchargé doit être signé par une autorité
 *  racine de confiance (liste de WordPress + celle du serveur), sinon la connexion échoue.
 * ------------------------------------------------------------------ */
function bdr_v17_fx_is_chain_error($err) {
  $m = $err->get_error_message();
  return stripos($m, 'error 60') !== false || stripos($m, 'local issuer certificate') !== false || stripos($m, 'unable to verify the first certificate') !== false;
}

function bdr_v17_fx_bundle_file() {
  $up = wp_upload_dir(null, false);
  return trailingslashit($up['basedir']) . 'bdr-fx/ca-bundle.pem';
}

/** Fichier de chaîne complétée encore valable (30 jours), ou ''. */
function bdr_v17_fx_saved_bundle() {
  $saved = get_option('bdr_fx_cabundle', array());
  if (empty($saved['time']) || time() - (int)$saved['time'] > 30 * DAY_IN_SECONDS) return '';
  $file = bdr_v17_fx_bundle_file();
  return is_readable($file) ? $file : '';
}

function bdr_v17_fx_der_to_pem($data) {
  if (strpos($data, '-----BEGIN CERTIFICATE-----') !== false) return $data;
  return "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode($data), 64, "\n") . "-----END CERTIFICATE-----\n";
}

function bdr_v17_fx_aia_url($pem) {
  $p = openssl_x509_parse($pem);
  $aia = isset($p['extensions']['authorityInfoAccess']) ? (string)$p['extensions']['authorityInfoAccess'] : '';
  return preg_match('#CA Issuers - URI:(https?://\S+)#i', $aia, $m) ? $m[1] : '';
}

/** Certificats intermédiaires : ceux envoyés par le serveur + ceux téléchargés via « CA Issuers » (3 niveaux max). */
function bdr_v17_fx_intermediates($url) {
  if (!function_exists('openssl_x509_parse') || !function_exists('stream_socket_client')) return array();
  $host = parse_url($url, PHP_URL_HOST); $port = parse_url($url, PHP_URL_PORT); $port = $port ? (int)$port : 443;
  if (!$host) return array();
  // Connexion de lecture seule pour récupérer les certificats présentés (aucune donnée n'est envoyée).
  $ctx = stream_context_create(array('ssl' => array('capture_peer_cert_chain' => true, 'verify_peer' => false, 'verify_peer_name' => false, 'SNI_enabled' => true, 'peer_name' => $host)));
  $fp = @stream_socket_client('ssl://' . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
  if (!$fp) return array();
  $params = stream_context_get_params($fp); fclose($fp);
  $chain = isset($params['options']['ssl']['peer_certificate_chain']) ? $params['options']['ssl']['peer_certificate_chain'] : array();
  $pems = array();
  foreach ($chain as $c) { $pem = ''; if (openssl_x509_export($c, $pem)) $pems[] = $pem; }
  if (!$pems) return array();
  $out = array_slice($pems, 1); $cur = end($pems);
  for ($i = 0; $i < 3; $i++) {
    $aia = bdr_v17_fx_aia_url($cur);
    if ($aia === '') break;
    $r = wp_safe_remote_get($aia, array('timeout' => 15, 'redirection' => 3));
    if (is_wp_error($r) || 200 !== (int)wp_remote_retrieve_response_code($r)) break;
    $pem = bdr_v17_fx_der_to_pem(wp_remote_retrieve_body($r));
    if (!@openssl_x509_read($pem)) break;
    $out[] = $pem; $cur = $pem;
    $p = openssl_x509_parse($pem);
    if (isset($p['subject'], $p['issuer']) && $p['subject'] == $p['issuer']) break; // racine atteinte
  }
  return $out;
}

/** Construit wp-content/uploads/bdr-fx/ca-bundle.pem (racines WordPress + serveur + intermédiaires). Retourne le chemin ou ''. */
function bdr_v17_fx_ca_bundle($url) {
  $inter = bdr_v17_fx_intermediates($url);
  if (!$inter) return '';
  $roots = (string)@file_get_contents(ABSPATH . WPINC . '/certificates/ca-bundle.crt');
  $loc = function_exists('openssl_get_cert_locations') ? openssl_get_cert_locations() : array();
  if (!empty($loc['default_cert_file']) && is_readable($loc['default_cert_file'])) $roots .= "\n" . (string)@file_get_contents($loc['default_cert_file']);
  if ($roots === '') return '';
  $file = bdr_v17_fx_bundle_file();
  if (!wp_mkdir_p(dirname($file))) return '';
  if (false === @file_put_contents($file, $roots . "\n" . implode("\n", $inter))) return '';
  update_option('bdr_fx_cabundle', array('time' => time(), 'count' => count($inter)), false);
  return $file;
}

/** Télécharge et enregistre. Retourne array('ok'=>bool,'message'=>string,'count'=>int). Les anciens cours sont conservés en cas d'échec. */
function bdr_v16_fx_update($html = null) {
  $chain_note = '';
  if ($html === null) {
    $url = bdr_v16_fx_source();
    $args = array(
      'timeout' => 25, 'redirection' => 5, 'sslverify' => true,
      'user-agent' => 'BDR-site/17 (+' . home_url('/') . '; mise a jour quotidienne des cours)',
      'headers' => array('Accept' => 'text/html,application/xhtml+xml', 'Accept-Language' => 'fr,ar;q=0.8,en;q=0.5'),
    );
    // Chaîne de certificats déjà complétée lors d'un passage précédent (voir bdr_v17_fx_ca_bundle()).
    $bundle = bdr_v17_fx_saved_bundle();
    if ($bundle) $args['sslcertificates'] = $bundle;
    $res = wp_safe_remote_get($url, $args);
    // Serveur source qui n'envoie pas son certificat intermédiaire (cURL error 60) : on complète la chaîne, comme un navigateur.
    if (is_wp_error($res) && bdr_v17_fx_is_chain_error($res)) {
      $bundle = bdr_v17_fx_ca_bundle($url);
      if ($bundle) { $args['sslcertificates'] = $bundle; $res = wp_safe_remote_get($url, $args); }
    }
    // Certains pare-feu refusent les robots déclarés : seconde tentative avec un navigateur standard.
    if (is_wp_error($res) || in_array((int)wp_remote_retrieve_response_code($res), array(403, 406, 429, 503), true)) {
      $args['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';
      $res = wp_safe_remote_get($url, $args);
    }
    if (is_wp_error($res)) { $st = bdr_v16_fx_set_status(false, 'Connexion impossible : ' . $res->get_error_message()); return array('ok' => false, 'message' => $st['message'], 'count' => 0); }
    $code = (int)wp_remote_retrieve_response_code($res);
    if ($code !== 200) { $st = bdr_v16_fx_set_status(false, 'Réponse HTTP ' . $code . ' de la source.'); return array('ok' => false, 'message' => $st['message'], 'count' => 0); }
    $html = wp_remote_retrieve_body($res);
    if (!empty($args['sslcertificates'])) $chain_note = ' Chaîne de certificats de la source complétée automatiquement.';
  }
  $p = bdr_v16_fx_parse($html);
  $why = array('empty' => 'Page vide ou trop courte.', 'no_dom' => 'Extension PHP DOM absente.', 'no_table' => 'Aucun tableau de cours reconnu (page modifiée ou contenu chargé par JavaScript).', 'implausible' => 'Valeurs non plausibles : tableau non reconnu.');
  if ($p['error']) { $msg = isset($why[$p['error']]) ? $why[$p['error']] : 'Analyse impossible.'; bdr_v16_fx_set_status(false, $msg); return array('ok' => false, 'message' => $msg, 'count' => 0); }
  $date = $p['date'] ? $p['date'] : wp_date('Y-m-d', null, new DateTimeZone('Africa/Algiers'));
  update_option('bdr_fx_auto', array('rows' => $p['rows'], 'date' => $date, 'date_found' => (bool)$p['date'], 'fetched' => time(), 'source' => bdr_v16_fx_source()), false);
  $msg = count($p['rows']) . ' devises enregistrées' . ($p['date'] ? ' (cours du ' . $date . ').' : ' (date non trouvée sur la page : date du jour utilisée).');
  $msg .= $chain_note;
  bdr_v16_fx_set_status(true, $msg, count($p['rows']));
  bdr_v17_fx_purge_cache();
  return array('ok' => true, 'message' => $msg, 'count' => count($p['rows']));
}

/** Nouveaux cours : vider le cache des pages pour qu'ils s'affichent tout de suite (LiteSpeed sur o2switch, WP Rocket…). */
function bdr_v17_fx_purge_cache() {
  do_action('litespeed_purge_all');
  if (function_exists('rocket_clean_domain')) rocket_clean_domain();
  if (function_exists('wp_cache_clear_cache')) wp_cache_clear_cache();
}

/** Planification : une fois par jour (≈ 09 h 05, heure d'Alger) ; rattrapage discret si la tâche n'a pas tourné depuis plus de 30 h. */
function bdr_v16_fx_schedule() {
  if (get_option('bdr_fx_mode', 'auto') !== 'auto') { if (wp_next_scheduled(BDR_FX_HOOK)) wp_clear_scheduled_hook(BDR_FX_HOOK); return; }
  if (!wp_next_scheduled(BDR_FX_HOOK)) {
    $tz = new DateTimeZone('Africa/Algiers'); $d = new DateTime('today 09:05', $tz);
    if ($d->getTimestamp() <= time() + 60) $d->modify('+1 day');
    wp_schedule_event($d->getTimestamp(), 'daily', BDR_FX_HOOK);
  }
  $st = get_option('bdr_fx_status', array());
  $last = isset($st['time']) ? (int)$st['time'] : 0;
  if (time() - $last > 30 * HOUR_IN_SECONDS && !get_transient('bdr_fx_catchup')) {
    set_transient('bdr_fx_catchup', 1, 6 * HOUR_IN_SECONDS);
    wp_schedule_single_event(time() + 20, BDR_FX_HOOK);
  }
}
add_action('init', 'bdr_v16_fx_schedule', 20);
add_action(BDR_FX_HOOK, function () { if (get_option('bdr_fx_mode', 'auto') === 'auto') bdr_v16_fx_update(); });
add_action('switch_theme', function () { wp_clear_scheduled_hook(BDR_FX_HOOK); });

/* ------------------------------------------------------------------ *
 *  Données affichées sur le site
 * ------------------------------------------------------------------ */
/** Retourne array('rows' => [...], 'date' => 'AAAA-MM-JJ', 'auto' => bool). */
function bdr_v16_fx_dataset() {
  static $cache = null; if ($cache !== null) return $cache;
  $auto = get_option('bdr_fx_auto', array());
  if (get_option('bdr_fx_mode', 'auto') === 'auto' && is_array($auto) && !empty($auto['rows'])) {
    return $cache = array('rows' => $auto['rows'], 'date' => $auto['date'], 'auto' => true, 'fetched' => isset($auto['fetched']) ? (int)$auto['fetched'] : 0);
  }
  $saved = get_option('bdr_fx_rows');
  $rows = (is_array($saved) && count($saved)) ? $saved : bdr_v15_fx_defaults();
  return $cache = array('rows' => $rows, 'date' => bdr_v15_opt('fx_date'), 'auto' => false, 'fetched' => 0);
}

/* ------------------------------------------------------------------ *
 *  Administration › Taux de change
 * ------------------------------------------------------------------ */
function bdr_v16_fx_menu() { add_menu_page('Taux de change', 'Taux de change', 'manage_options', 'bdr-rates', 'bdr_v16_fx_admin', 'dashicons-chart-line', 26); }
add_action('admin_menu', 'bdr_v16_fx_menu');

function bdr_v16_fx_admin() {
  if (!current_user_can('manage_options')) return;
  $notice = '';
  if (isset($_POST['bdr_fx_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bdr_fx_nonce'])), 'bdr_fx_admin')) {
    $act = isset($_POST['bdr_fx_action']) ? sanitize_key(wp_unslash($_POST['bdr_fx_action'])) : 'save';
    if ($act === 'update') {
      $r = bdr_v16_fx_update();
      $notice = '<div class="notice ' . ($r['ok'] ? 'notice-success' : 'notice-error') . '"><p>' . esc_html($r['message']) . '</p></div>';
    } else {
      $mode = (isset($_POST['fx_mode']) && $_POST['fx_mode'] === 'manual') ? 'manual' : 'auto';
      update_option('bdr_fx_mode', $mode);
      $src = esc_url_raw(trim((string)wp_unslash($_POST['fx_source'] ?? '')));
      update_option('bdr_fx_source', preg_match('#^https://#i', $src) ? $src : '');
      $new = array();
      foreach ((array)($_POST['code'] ?? array()) as $i => $code) {
        $code = strtoupper(preg_replace('/[^A-Za-z]/', '', wp_unslash($code))); $val = preg_replace('/[^0-9,\.]/', '', wp_unslash($_POST['value'][$i] ?? ''));
        if ($code !== '' && $val !== '') $new[] = array('code' => substr($code, 0, 3), 'value' => $val);
      }
      if ($new) update_option('bdr_fx_rows', $new);
      $d = sanitize_text_field(wp_unslash($_POST['fx_date'] ?? ''));
      if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) set_theme_mod('bdr_fx_date', $d);
      bdr_v16_fx_schedule();
      $notice = '<div class="notice notice-success"><p>Réglages enregistrés.</p></div>';
    }
  }
  $st = get_option('bdr_fx_status', array()); $auto = get_option('bdr_fx_auto', array()); $mode = get_option('bdr_fx_mode', 'auto');
  $fmt = function ($ts) { return $ts ? date_i18n('d/m/Y H:i', $ts) : '—'; };
  echo '<div class="wrap"><h1>Taux de change</h1>' . $notice;
  echo '<p>Les cours de l’accueil et de la page « Taux de change » sont récupérés <strong>une fois par jour</strong> sur la page de la Banque d’Algérie, puis affichés comme <strong>indicatifs</strong>. En cas d’échec, les derniers cours valides restent affichés.</p>';
  echo '<table class="widefat striped" style="max-width:720px"><tbody>';
  echo '<tr><th style="width:230px">Dernière tentative</th><td>' . esc_html($fmt($st['time'] ?? 0)) . ' — ' . (empty($st) ? 'jamais exécutée' : (!empty($st['ok']) ? '<span style="color:#0a7a5c">réussie</span>' : '<span style="color:#b42318">échec</span>')) . '</td></tr>';
  echo '<tr><th>Message</th><td>' . esc_html($st['message'] ?? '—') . '</td></tr>';
  echo '<tr><th>Dernière réussite</th><td>' . esc_html($fmt($st['last_ok'] ?? 0)) . '</td></tr>';
  echo '<tr><th>Cours en mémoire</th><td>' . (!empty($auto['rows']) ? (int)count($auto['rows']) . ' devises, date des cours : ' . esc_html($auto['date']) : 'aucun (les cours de secours ci-dessous sont utilisés)') . '</td></tr>';
  echo '<tr><th>Prochaine exécution</th><td>' . esc_html($fmt((int)wp_next_scheduled(BDR_FX_HOOK))) . '</td></tr>';
  echo '</tbody></table>';
  echo '<form method="post" style="margin-top:14px">' . wp_nonce_field('bdr_fx_admin', 'bdr_fx_nonce', true, false) . '<input type="hidden" name="bdr_fx_action" value="update"><button class="button button-primary">Mettre à jour maintenant</button></form><hr>';
  echo '<form method="post">' . wp_nonce_field('bdr_fx_admin', 'bdr_fx_nonce', true, false) . '<input type="hidden" name="bdr_fx_action" value="save">';
  echo '<h2>Réglages</h2><p><label><input type="radio" name="fx_mode" value="auto"' . ($mode === 'auto' ? ' checked' : '') . '> <strong>Automatique</strong> (recommandé) — mise à jour quotidienne</label><br><label><input type="radio" name="fx_mode" value="manual"' . ($mode === 'manual' ? ' checked' : '') . '> <strong>Manuel</strong> — uniquement les cours saisis ci-dessous</label></p>';
  echo '<p><label><strong>Adresse de la source</strong> (https)<br><input type="url" name="fx_source" class="regular-text" style="width:520px" value="' . esc_attr(get_option('bdr_fx_source', '') ?: bdr_v16_fx_source()) . '"></label></p>';
  echo '<h2>Cours de secours / saisie manuelle</h2><p>Utilisés en mode manuel, ou tant qu’aucun cours automatique n’a été récupéré.</p>';
  echo '<p><label><strong>Date des cours</strong> <input type="date" name="fx_date" value="' . esc_attr(bdr_v15_opt('fx_date')) . '"></label></p>';
  $saved = get_option('bdr_fx_rows'); $rows = (is_array($saved) && count($saved)) ? $saved : bdr_v15_fx_defaults();
  echo '<table class="widefat" style="max-width:520px"><thead><tr><th>Devise</th><th>Cours en DZD (ex. 153,2126)</th></tr></thead><tbody>';
  foreach ($rows as $i => $r) echo '<tr><td><input name="code[' . (int)$i . ']" value="' . esc_attr($r['code']) . '" size="4" maxlength="3"></td><td><input name="value[' . (int)$i . ']" value="' . esc_attr($r['value']) . '"></td></tr>';
  echo '</tbody></table><p><button class="button button-primary">Enregistrer</button></p></form>';
  echo '<p class="description">Astuce hébergement : pour garantir l’exécution à heure fixe même sans visite, programmez une tâche cron côté serveur (o2switch › Tâches cron) : <code>wget -q -O - ' . esc_html(home_url('/wp-cron.php?doing_wp_cron')) . ' &gt;/dev/null 2&gt;&amp;1</code> toutes les 15 min.</p></div>';
}
