<?php
/**
 * BDR V16 — page « Banque en ligne » : connexion client avec CAPTCHA.
 *
 * Principes de sécurité
 *  - Les identifiants ne transitent JAMAIS par WordPress : le formulaire est envoyé directement au portail
 *    officiel BDR-NET (URL saisie dans Apparence › Personnaliser › BDR – Coordonnées & liens).
 *  - Le CAPTCHA est généré par le serveur (image composée de tracés, sans texte lisible dans le code),
 *    signé HMAC, à durée de vie courte et à usage unique ; il est chargé en AJAX (compatible cache de page).
 *  - Sans JavaScript, aucun champ n'a d'attribut « name » : rien ne peut être envoyé par erreur.
 *  - Trois modes selon la configuration : « direct » (POST vers BDR-NET), « portail » (CAPTCHA puis redirection
 *    vers la page de connexion de BDR-NET) et « inactif » (message, aucun envoi).
 *  Limite à connaître : ce CAPTCHA protège cette page ; il ne remplace pas les contrôles propres à BDR-NET.
 */
if (!defined('ABSPATH')) exit;

/* ------------------------------------------------------------------ *
 *  Réglages
 * ------------------------------------------------------------------ */
function bdr_v16_login_cfg() {
  $post = bdr_v15_opt('ebanking_post'); $portal = bdr_v15_opt('ebanking');
  $post = (is_string($post) && preg_match('#^https://#i', $post)) ? $post : '';
  $portal = (is_string($portal) && preg_match('#^https://#i', $portal)) ? $portal : '';
  $user = preg_replace('/[^A-Za-z0-9_\-\[\]\.]/', '', (string)bdr_v15_opt('ebanking_user', 'username')); if ($user === '') $user = 'username';
  $pass = preg_replace('/[^A-Za-z0-9_\-\[\]\.]/', '', (string)bdr_v15_opt('ebanking_pass', 'password')); if ($pass === '') $pass = 'password';
  $mode = $post ? 'direct' : ($portal ? 'portal' : 'off');
  return array('mode' => $mode, 'post' => $post, 'portal' => $portal ? $portal : ($post ? $post : ''), 'user' => $user, 'pass' => $pass);
}
function bdr_v16_origin($url) {
  $p = @parse_url((string)$url);
  if (!$p || empty($p['scheme']) || empty($p['host'])) return '';
  return $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
}
function bdr_v16_is_online_route() { return bdr_v15_current_slug() === 'banque-en-ligne'; }

/* ------------------------------------------------------------------ *
 *  CAPTCHA : glyphes tracés (aucun <text> : le code n'est pas lisible dans le SVG)
 * ------------------------------------------------------------------ */
function bdr_v16_glyphs() {
  return array(
    '2' => 'M4,14 Q4,3 20,3 Q36,3 36,16 Q36,26 20,38 L4,57 L37,57',
    '3' => 'M5,8 Q12,3 20,3 Q35,3 35,16 Q35,28 18,29 Q37,30 37,43 Q37,57 20,57 Q8,57 3,50',
    '4' => 'M30,57 L30,3 L3,40 L38,40',
    '5' => 'M35,4 L8,4 L6,28 Q14,24 22,24 Q37,24 37,41 Q37,57 20,57 Q8,57 3,50',
    '6' => 'M33,8 Q26,3 20,3 Q5,5 5,36 Q5,57 21,57 Q36,57 36,41 Q36,26 21,26 Q10,26 5,36',
    '7' => 'M3,4 L37,4 Q22,28 16,57',
    '8' => 'M20,3 Q7,3 7,15 Q7,27 20,29 Q35,31 35,43 Q35,57 20,57 Q5,57 5,43 Q5,31 20,29 Q33,27 33,15 Q33,3 20,3',
    '9' => 'M7,52 Q14,57 20,57 Q35,55 35,24 Q35,3 19,3 Q4,3 4,19 Q4,34 19,34 Q30,34 35,24',
    'A' => 'M3,57 L20,3 L37,57 M10,38 L30,38',
    'B' => 'M6,57 L6,3 L22,3 Q34,3 34,15 Q34,28 20,29 L6,29 M20,29 Q37,29 37,43 Q37,57 22,57 L6,57',
    'C' => 'M35,12 Q30,3 20,3 Q5,3 5,30 Q5,57 20,57 Q31,57 36,48',
    'D' => 'M6,3 L6,57 L18,57 Q36,57 36,30 Q36,3 18,3 L6,3',
    'E' => 'M35,3 L7,3 L7,57 L35,57 M7,30 L28,30',
    'F' => 'M35,3 L7,3 L7,57 M7,30 L28,30',
    'G' => 'M35,12 Q30,3 20,3 Q5,3 5,30 Q5,57 20,57 Q36,57 36,34 L22,34',
    'H' => 'M6,3 L6,57 M34,3 L34,57 M6,30 L34,30',
    'J' => 'M30,3 L30,43 Q30,57 18,57 Q8,57 5,46',
    'K' => 'M6,3 L6,57 M34,3 L6,34 M14,26 L36,57',
    'L' => 'M7,3 L7,57 L35,57',
    'M' => 'M4,57 L4,3 L20,36 L36,3 L36,57',
    'N' => 'M6,57 L6,3 L34,57 L34,3',
    'P' => 'M6,57 L6,3 L22,3 Q36,3 36,17 Q36,31 22,31 L6,31',
    'R' => 'M6,57 L6,3 L22,3 Q36,3 36,17 Q36,31 22,31 L6,31 M20,31 L36,57',
    'S' => 'M34,10 Q28,3 19,3 Q5,3 5,16 Q5,27 20,30 Q36,34 36,44 Q36,57 20,57 Q9,57 4,48',
    'T' => 'M3,3 L37,3 M20,3 L20,57',
    'U' => 'M5,3 L5,40 Q5,57 20,57 Q35,57 35,40 L35,3',
    'V' => 'M3,3 L20,57 L37,3',
    'W' => 'M2,3 L11,57 L20,20 L29,57 L38,3',
    'X' => 'M4,3 L36,57 M36,3 L4,57',
    'Y' => 'M3,3 L20,30 L37,3 M20,30 L20,57',
    'Z' => 'M4,3 L36,3 L4,57 L36,57',
  );
}

function bdr_v16_rand($a, $b) { return $a + (random_int(0, 10000) / 10000) * ($b - $a); }

/** Image SVG (URI data:) pour un code donné. */
function bdr_v16_captcha_svg($code) {
  $g = bdr_v16_glyphs(); $W = 240; $H = 76; $n = strlen($code);
  $cols = array('#062f36', '#0a5f57', '#0a7a5c', '#1d6f8c', '#4a3a08');
  $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">';
  $svg .= '<defs><linearGradient id="b" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#eef6f5"/><stop offset="1" stop-color="#e3eef0"/></linearGradient></defs><rect width="' . $W . '" height="' . $H . '" fill="url(#b)"/>';
  for ($i = 0; $i < 38; $i++) $svg .= '<circle cx="' . round(bdr_v16_rand(0, $W), 1) . '" cy="' . round(bdr_v16_rand(0, $H), 1) . '" r="' . round(bdr_v16_rand(.6, 1.8), 1) . '" fill="' . $cols[random_int(0, 4)] . '" opacity=".22"/>';
  for ($i = 0; $i < 3; $i++) { // courbes parasites sous le texte
    $y = bdr_v16_rand(14, $H - 14);
    $svg .= '<path d="M-5,' . round($y, 1) . ' C' . round(bdr_v16_rand(40, 90), 1) . ',' . round($y + bdr_v16_rand(-30, 30), 1) . ' ' . round(bdr_v16_rand(130, 190), 1) . ',' . round($y + bdr_v16_rand(-30, 30), 1) . ' ' . ($W + 5) . ',' . round(bdr_v16_rand(10, $H - 10), 1) . '" fill="none" stroke="' . $cols[random_int(0, 4)] . '" stroke-width="' . round(bdr_v16_rand(1, 2), 1) . '" opacity=".35"/>';
  }
  $step = ($W - 30) / $n;
  for ($i = 0; $i < $n; $i++) {
    $ch = $code[$i]; if (!isset($g[$ch])) continue;
    $d = preg_replace_callback('/(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', function ($m) { return round($m[1] + bdr_v16_rand(-1.6, 1.6), 1) . ',' . round($m[2] + bdr_v16_rand(-1.6, 1.6), 1); }, $g[$ch]);
    $sx = bdr_v16_rand(.66, .86); $sy = bdr_v16_rand(.72, .9);
    $x = 18 + $i * $step + bdr_v16_rand(-3, 3); $y = 8 + bdr_v16_rand(-3, 5); $rot = bdr_v16_rand(-16, 16); $sk = bdr_v16_rand(-14, 14);
    $svg .= '<g transform="translate(' . round($x, 1) . ' ' . round($y, 1) . ') rotate(' . round($rot, 1) . ' 14 26) skewX(' . round($sk, 1) . ') scale(' . round($sx, 2) . ' ' . round($sy, 2) . ')">'
      . '<path d="' . $d . '" fill="none" stroke="' . $cols[random_int(0, 4)] . '" stroke-width="' . round(bdr_v16_rand(3.6, 5), 1) . '" stroke-linecap="round" stroke-linejoin="round"/></g>';
  }
  for ($i = 0; $i < 4; $i++) { // traits parasites au-dessus
    $svg .= '<path d="M' . round(bdr_v16_rand(0, 60), 1) . ',' . round(bdr_v16_rand(5, $H - 5), 1) . ' Q' . round(bdr_v16_rand(70, 170), 1) . ',' . round(bdr_v16_rand(0, $H), 1) . ' ' . round(bdr_v16_rand(180, $W), 1) . ',' . round(bdr_v16_rand(5, $H - 5), 1) . '" fill="none" stroke="' . $cols[random_int(0, 4)] . '" stroke-width="1.4" opacity=".55"/>';
  }
  $svg .= '</svg>';
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/* ------------------------------------------------------------------ *
 *  Jetons signés
 * ------------------------------------------------------------------ */
function bdr_v16_b64u($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function bdr_v16_b64u_dec($s) { return base64_decode(strtr($s, '-_', '+/')); }
function bdr_v16_secret() { return function_exists('wp_salt') ? wp_salt('auth') : 'bdr-dev-secret'; }
function bdr_v16_sign($payload, $answer) { return hash_hmac('sha256', $payload . '|' . $answer, bdr_v16_secret()); }

function bdr_v16_captcha_question($lang, &$answer) {
  $a = random_int(3, 9); $b = random_int(2, 8); $plus = random_int(0, 1) === 1;
  if (!$plus && $a <= $b) { $t = $a; $a = $b + 1; $b = $t; }
  $answer = (string)($plus ? $a + $b : $a - $b);
  $op = $plus ? '+' : '−';
  if ($lang === 'ar') return 'كم يساوي ' . $a . ' ' . $op . ' ' . $b . ' ؟ (اكتب الرقم)';
  if ($lang === 'en') return 'What is ' . $a . ' ' . $op . ' ' . $b . '? (type the number)';
  return 'Combien font ' . $a . ' ' . $op . ' ' . $b . ' ? (saisissez le nombre)';
}

/** Nouveau défi. $kind = 'image' | 'text'. Retourne array(token, image|question). */
function bdr_v16_captcha_new($kind = 'image', $lang = 'fr') {
  $exp = time() + 600; $nonce = bin2hex(random_bytes(6));
  if ($kind === 'text') {
    $answer = ''; $question = bdr_v16_captcha_question($lang, $answer);
    $payload = bdr_v16_b64u(json_encode(array('e' => $exp, 'n' => $nonce, 'k' => 't')));
    return array('token' => $payload . '.' . bdr_v16_sign($payload, $answer), 'question' => $question, 'kind' => 'text');
  }
  $alpha = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789'; $code = '';
  for ($i = 0; $i < 5; $i++) $code .= $alpha[random_int(0, strlen($alpha) - 1)];
  $payload = bdr_v16_b64u(json_encode(array('e' => $exp, 'n' => $nonce, 'k' => 'i')));
  return array('token' => $payload . '.' . bdr_v16_sign($payload, $code), 'image' => bdr_v16_captcha_svg($code), 'kind' => 'image');
}

/** Vérifie la réponse (insensible à la casse et aux espaces). Usage unique. */
function bdr_v16_captcha_check($token, $answer) {
  $answer = strtoupper(preg_replace('/[\s\-]+/', '', (string)$answer));
  if ($answer === '' || strlen($answer) > 12) return false;
  $parts = explode('.', (string)$token);
  if (count($parts) !== 2) return false;
  $data = json_decode((string)bdr_v16_b64u_dec($parts[0]), true);
  if (!is_array($data) || empty($data['e']) || (int)$data['e'] < time()) return false;
  if (!hash_equals(bdr_v16_sign($parts[0], $answer), (string)$parts[1])) return false;
  $key = 'bdr_cap_' . md5($parts[1]);
  if (get_transient($key)) return false;   // déjà utilisé
  set_transient($key, 1, 900);
  return true;
}

/** Limitation simple par adresse IP (seau glissant via transient). */
function bdr_v16_rate_ok($bucket, $max, $window) {
  $ip = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '0';
  $key = 'bdr_rl_' . $bucket . '_' . md5($ip);
  $n = (int)get_transient($key);
  if ($n >= $max) return false;
  set_transient($key, $n + 1, $window);
  return true;
}

function bdr_v16_ajax_new() {
  nocache_headers();
  if (!bdr_v16_rate_ok('new', 40, 600)) wp_send_json_error(array('code' => 'rate'), 429);
  $kind = (isset($_GET['kind']) && $_GET['kind'] === 'text') ? 'text' : 'image';
  $lang = isset($_GET['lang']) ? sanitize_key(wp_unslash($_GET['lang'])) : 'fr'; if (!in_array($lang, array('fr', 'en', 'ar'), true)) $lang = 'fr';
  wp_send_json_success(bdr_v16_captcha_new($kind, $lang));
}
function bdr_v16_ajax_check() {
  nocache_headers();
  if (!bdr_v16_rate_ok('chk', 25, 600)) wp_send_json_error(array('code' => 'rate'), 429);
  $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
  $answer = isset($_POST['answer']) ? sanitize_text_field(wp_unslash($_POST['answer'])) : '';
  if (!empty($_POST['website'])) wp_send_json_error(array('code' => 'bot'), 400);   // champ piège rempli
  if (bdr_v16_captcha_check($token, $answer)) wp_send_json_success(array('ok' => true));
  wp_send_json_error(array('code' => 'wrong'), 200);
}
add_action('wp_ajax_nopriv_bdr_captcha_new', 'bdr_v16_ajax_new');   add_action('wp_ajax_bdr_captcha_new', 'bdr_v16_ajax_new');
add_action('wp_ajax_nopriv_bdr_captcha_check', 'bdr_v16_ajax_check'); add_action('wp_ajax_bdr_captcha_check', 'bdr_v16_ajax_check');

/** Configuration transmise au JS (bloc JSON). */
function bdr_v16_login_js_config($lang) {
  if (!bdr_v16_is_online_route()) return null;
  if (function_exists('bdr_net_login_card')) return null; // extension BDR-NET : connexion gérée par l'extension
  $c = bdr_v16_login_cfg();
  return array(
    'mode' => $c['mode'], 'post' => $c['post'], 'portal' => $c['portal'], 'user' => $c['user'], 'pass' => $c['pass'],
    'msg' => array(
      'empty'  => bdr_v15_t('Renseignez votre identifiant, votre mot de passe et le code de sécurité.', 'Enter your username, password and security code.', 'أدخل معرّفك وكلمة المرور ورمز الأمان.', $lang),
      'emptyc' => bdr_v15_t('Saisissez le code de sécurité affiché.', 'Enter the security code shown.', 'أدخل رمز الأمان المعروض.', $lang),
      'wrong'  => bdr_v15_t('Code de sécurité incorrect. Un nouveau code a été généré.', 'Incorrect security code. A new code has been generated.', 'رمز الأمان غير صحيح. تم إنشاء رمز جديد.', $lang),
      'rate'   => bdr_v15_t('Trop de tentatives. Patientez quelques minutes avant de réessayer.', 'Too many attempts. Please wait a few minutes.', 'محاولات كثيرة. يرجى الانتظار بضع دقائق.', $lang),
      'net'    => bdr_v15_t('Vérification impossible pour le moment. Réessayez.', 'Verification unavailable right now. Please try again.', 'تعذر التحقق حالياً. أعد المحاولة.', $lang),
      'sending'=> bdr_v15_t('Vérification…', 'Checking…', 'جارٍ التحقق…', $lang),
      'ok'     => bdr_v15_t('Code valide. Redirection vers le portail sécurisé…', 'Code valid. Redirecting to the secure portal…', 'الرمز صحيح. جارٍ التحويل إلى البوابة الآمنة…', $lang),
      'show'   => bdr_v15_t('Afficher le mot de passe', 'Show password', 'إظهار كلمة المرور', $lang),
      'hide'   => bdr_v15_t('Masquer le mot de passe', 'Hide password', 'إخفاء كلمة المرور', $lang),
      'toText' => bdr_v15_t('Code illisible ? Utiliser une question simple', 'Can’t read it? Use a simple question instead', 'الرمز غير واضح؟ استخدم سؤالاً بسيطاً', $lang),
      'toImg'  => bdr_v15_t('Revenir au code en image', 'Back to the image code', 'العودة إلى الرمز المصوّر', $lang),
      'off'    => bdr_v15_t('Code valide. Le portail BDR-NET n’est pas encore relié à ce site : contactez votre agence pour accéder à vos comptes.', 'Code valid. The BDR-NET portal is not yet linked to this site: please contact your branch to access your accounts.', 'الرمز صحيح. بوابة BDR-NET غير مرتبطة بهذا الموقع بعد: تواصل مع وكالتك للوصول إلى حساباتك.', $lang),
      'loading'=> bdr_v15_t('Chargement du code…', 'Loading code…', 'جارٍ تحميل الرمز…', $lang),
    ),
  );
}

/* ------------------------------------------------------------------ *
 *  Page « Banque en ligne »
 * ------------------------------------------------------------------ */
function bdr_v15_render_online_page($lang) {
  $u = bdr_v11_ui($lang); $c = bdr_v16_login_cfg(); $T = function ($fr, $en, $ar) use ($lang) { return bdr_v15_t($fr, $en, $ar, $lang); };
  $bdr_net = function_exists('bdr_net_login_card'); // extension « BDR-NET Banque en ligne » active
  bdr_page_hero($T('Espace client', 'Customer area', 'فضاء العميل'), $T('Banque en ligne', 'Online banking', 'الخدمات المصرفية عبر الإنترنت'),
    $bdr_net
      ? $T('Connectez-vous à BDR-NET pour suivre vos dossiers, déposer vos documents et échanger avec votre conseiller, en toute sécurité.', 'Sign in to BDR-NET to track your cases, upload your documents and talk to your advisor securely.', 'سجّل الدخول إلى BDR-NET لمتابعة ملفاتك وإيداع وثائقك والتواصل مع مستشارك بأمان.')
      : $T('Connectez-vous à BDR-NET pour consulter vos comptes et effectuer vos opérations à distance, en toute sécurité.', 'Sign in to BDR-NET to view your accounts and carry out remote operations securely.', 'سجّل الدخول إلى BDR-NET للاطلاع على حساباتك وإجراء عملياتك عن بعد بأمان.'));
  echo '<section class="section login-section"><div class="container"><div class="login-shell">';

  // Colonne d'information (sombre)
  echo '<aside class="login-side"><span class="login-side-ic">' . bdr_v15_icon('lock', 'ic ic-lg') . '</span>';
  echo '<h2>' . esc_html($bdr_net ? $T('Un accès protégé à votre espace client', 'Protected access to your customer area', 'دخول محمي إلى فضاء العميل') : $T('Un accès protégé, sur le portail officiel', 'Protected access, on the official portal', 'دخول محمي عبر البوابة الرسمية')) . '</h2>';
  echo '<ul class="login-tips">';
  foreach (array(
    array($T('Vérifiez l’adresse', 'Check the address', 'تحقق من العنوان'), $T('Cadenas fermé et nom de domaine officiel dans la barre du navigateur.', 'Closed padlock and official domain name in the browser bar.', 'قفل مغلق واسم النطاق الرسمي في شريط المتصفح.')),
    array($T('Ne partagez jamais vos codes', 'Never share your codes', 'لا تشارك رموزك أبداً'), $T('La BDR ne vous demandera jamais votre mot de passe ni un code SMS, par e-mail, téléphone ou message.', 'BDR will never ask for your password or an SMS code by email, phone or message.', 'لن يطلب منك BDR كلمة المرور أو رمز الرسائل القصيرة عبر البريد أو الهاتف أو الرسائل.')),
    array($T('Déconnectez-vous', 'Sign out', 'سجّل الخروج'), $T('Fermez votre session après chaque utilisation, surtout sur un appareil partagé.', 'Close your session after each use, especially on a shared device.', 'أغلق جلستك بعد كل استخدام، خاصة على جهاز مشترك.')),
  ) as $tip) echo '<li>' . bdr_v15_icon('check') . '<span><strong>' . esc_html($tip[0]) . '</strong> ' . esc_html($tip[1]) . '</span></li>';
  echo '</ul><div class="login-side-links"><a href="' . esc_url(bdr_v11_url('securite-digitale', $lang)) . '">' . esc_html(bdr_v11_title('securite-digitale', $lang)) . ' ' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</a>';
  echo '<a href="' . esc_url(bdr_v11_url('ouvrir-un-compte', $lang)) . '">' . esc_html($T('Pas encore client ? Ouvrir un compte', 'Not a client yet? Open an account', 'لست عميلاً؟ افتح حساباً')) . ' ' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</a></div></aside>';

  // Formulaire
  echo '<div class="login-card">';
  if ($bdr_net) {
    // Extension BDR-NET : identifiant + mot de passe + code de sécurité, connexion à l'espace client du site.
    echo bdr_net_login_card($lang);
  } else {
  echo '<h2>' . esc_html($T('Connexion à BDR-NET', 'Sign in to BDR-NET', 'الدخول إلى BDR-NET')) . '</h2>';
  if ($c['mode'] === 'off' && current_user_can('manage_options')) echo '<p class="admin-hint">Administrateur : le portail BDR-NET n’est pas encore relié. Renseignez « BDR-NET — adresse du portail » (ou l’adresse d’envoi du formulaire + les noms des champs) dans Apparence › Personnaliser › BDR – Coordonnées &amp; liens. Le formulaire et le code de sécurité fonctionnent déjà.</p>';
  echo '<form id="bdr-login" class="login-form" method="post" novalidate data-mode="' . esc_attr($c['mode']) . '" autocomplete="off">';
  if ($c['mode'] === 'direct') {
    echo '<div class="field"><label for="bdr-l-user">' . esc_html($T('Identifiant', 'Username', 'المعرّف')) . '</label><div class="field-in">' . bdr_v15_icon('user') . '<input id="bdr-l-user" type="text" data-role="user" autocomplete="username" autocapitalize="none" spellcheck="false" required></div></div>';
    echo '<div class="field"><label for="bdr-l-pass">' . esc_html($T('Mot de passe', 'Password', 'كلمة المرور')) . '</label><div class="field-in">' . bdr_v15_icon('lock') . '<input id="bdr-l-pass" type="password" data-role="pass" autocomplete="current-password" required><button type="button" class="field-toggle" id="bdr-l-eye" aria-controls="bdr-l-pass" aria-label="' . esc_attr($T('Afficher le mot de passe', 'Show password', 'إظهار كلمة المرور')) . '">' . bdr_v15_icon('eye') . '</button></div></div>';
  } else {
    echo '<p class="login-lead">' . esc_html($T('Saisissez le code ci-dessous, puis poursuivez vers la page de connexion sécurisée de la banque, où vous saisirez vos identifiants.', 'Enter the code below, then continue to the bank’s secure sign-in page, where you will enter your credentials.', 'أدخل الرمز أدناه ثم تابع إلى صفحة الدخول الآمنة للبنك حيث ستُدخل بياناتك.')) . '</p>';
  }
  // CAPTCHA
  echo '<div class="field captcha-field"><label for="bdr-l-cap">' . esc_html($T('Code de sécurité', 'Security code', 'رمز الأمان')) . '</label>';
  echo '<div class="captcha-box"><div class="captcha-view" id="bdr-cap-view" aria-live="polite"><img id="bdr-cap-img" alt="' . esc_attr($T('Code de sécurité : recopiez les 5 caractères de l’image', 'Security code: copy the 5 characters shown in the image', 'رمز الأمان: انسخ الأحرف الخمسة الظاهرة في الصورة')) . '" width="240" height="76" hidden><p class="captcha-question" id="bdr-cap-q" hidden></p><span class="captcha-wait" id="bdr-cap-wait">' . esc_html($T('Chargement du code…', 'Loading code…', 'جارٍ تحميل الرمز…')) . '</span></div>';
  echo '<button type="button" class="icon-btn captcha-refresh" id="bdr-cap-refresh" aria-label="' . esc_attr($T('Nouveau code', 'New code', 'رمز جديد')) . '">' . bdr_v15_icon('refresh') . '</button></div>';
  echo '<input id="bdr-l-cap" class="captcha-input" type="text" inputmode="text" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="12" required aria-describedby="bdr-cap-help">';
  echo '<p class="field-help" id="bdr-cap-help"><button type="button" class="linklike" id="bdr-cap-mode">' . esc_html($T('Code illisible ? Utiliser une question simple', 'Can’t read it? Use a simple question instead', 'الرمز غير واضح؟ استخدم سؤالاً بسيطاً')) . '</button></p></div>';
  echo '<div class="bdr-hp" aria-hidden="true"><label>Website <input type="text" id="bdr-l-hp" tabindex="-1" autocomplete="off"></label></div>';
  echo '<p class="login-msg" id="bdr-l-msg" role="alert" hidden></p>';
  echo '<button class="btn btn-primary btn-lg btn-block" type="submit" id="bdr-l-go">' . bdr_v15_icon('lock') . ' <span>' . esc_html($c['mode'] === 'direct' ? $T('Se connecter', 'Sign in', 'تسجيل الدخول') : $T('Continuer vers BDR-NET', 'Continue to BDR-NET', 'المتابعة إلى BDR-NET')) . '</span></button>';
  echo '<noscript><p class="login-msg is-warn">' . esc_html($T('JavaScript est nécessaire pour vérifier le code de sécurité.', 'JavaScript is required to check the security code.', 'يلزم تفعيل JavaScript للتحقق من رمز الأمان.')) . '</p></noscript>';
  echo '</form>';
  $host = parse_url($c['mode'] === 'direct' ? $c['post'] : $c['portal'], PHP_URL_HOST);
  if ($c['mode'] !== 'off') echo '<p class="login-foot">' . bdr_v15_icon('shield') . ' <span>' . esc_html($T('Vos identifiants sont envoyés directement au portail sécurisé', 'Your credentials are sent directly to the secure portal', 'تُرسل بياناتك مباشرة إلى البوابة الآمنة')) . ($host ? ' <bdi dir="ltr">(' . esc_html($host) . ')</bdi>' : '') . ' ' . esc_html($T('et ne passent jamais par ce site.', 'and never pass through this site.', 'ولا تمر أبداً عبر هذا الموقع.')) . '</span></p>';
  }
  echo '</div></div></div></section>';
  bdr_v16_login_after($lang, $u);
}

function bdr_v16_login_after($lang, $u) {
  bdr_section(bdr_v15_t('Services digitaux', 'Digital services', 'الخدمات الرقمية', $lang));
  bdr_info_grid($lang === 'ar'
    ? array(array('BDR-NET', 'خدمات بنكية عن بعد وفق الوظائف المتاحة.'), array('الأمان', 'لا تشارك بيانات الدخول أو رموز الأمان مع أي شخص.'), array('التنبيهات', 'تابع التنبيهات والإشعارات المرتبطة بخدماتك.'), array('المساعدة', 'استخدم القنوات الرسمية عند الحاجة إلى المساعدة.'))
    : ($lang === 'en'
      ? array(array('BDR-NET', 'Remote banking services subject to the features available to you.'), array('Security', 'Never share credentials or security codes with anyone.'), array('Alerts', 'Keep track of alerts and notifications related to your services.'), array('Support', 'Use official channels whenever you need assistance.'))
      : array(array('BDR-NET', 'Des services bancaires à distance selon les fonctionnalités disponibles.'), array('Sécurité', 'Ne communiquez jamais vos identifiants ou codes de sécurité.'), array('Alertes', 'Suivez les alertes et notifications liées à vos services.'), array('Assistance', 'Utilisez les canaux officiels lorsque vous avez besoin d’aide.'))));
  bdr_close_section();
  bdr_section(bdr_v15_t('Aller plus loin', 'Go further', 'تابع الاستكشاف', $lang));
  bdr_v11_related(array(
    array($u['branches'], bdr_v11_url('agences', $lang), bdr_v15_t('Consultez le réseau d’agences.', 'Browse the branch network.', 'اكتشف شبكة الوكالات.', $lang)),
    array($u['contact'], bdr_v11_url('contact', $lang), bdr_v15_t('Demandez de l’aide.', 'Request assistance.', 'اطلب المساعدة.', $lang)),
    array(bdr_v11_title('securite-digitale', $lang), bdr_v11_url('securite-digitale', $lang), bdr_v15_t('Consultez les conseils de sécurité numérique.', 'Review digital-security guidance.', 'اطلع على نصائح الأمان الرقمي.', $lang)),
  ));
  bdr_close_section();
  bdr_v11_localized_bottom('banque-en-ligne', $lang);
}
