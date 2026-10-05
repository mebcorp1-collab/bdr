<?php
$lang = bdr_v11_lang();
$ui = bdr_v11_ui($lang);
$cur = bdr_v15_current_slug();
$phone = bdr_v15_opt('phone'); $phone_raw = bdr_v15_opt('phone_raw'); $email = bdr_v15_opt('email');

// Rubrique principale active (on remonte la hiérarchie jusqu'à une entrée du menu).
$tops = array(); foreach (bdr_v15_nav() as $t) $tops[$t['slug']] = true;
$active_top = ''; $walk = $cur; $guard = 0;
while ($walk && $guard++ < 8) { if (isset($tops[$walk])) { $active_top = $walk; break; } $walk = bdr_v11_parent_slug($walk); }

$shortcuts = array(
  array('banque-en-ligne', 'phone', bdr_v15_t('E-banking', 'E-banking', 'الخدمات الرقمية', $lang)),
  array('agences', 'pin', bdr_v15_t('Agences', 'Branches', 'الوكالات', $lang)),
  array('credits', 'calc', bdr_v15_t('Simulation', 'Simulation', 'محاكاة', $lang)),
  array('taux-de-change', 'exchange', bdr_v15_t('Change', 'Exchange', 'الصرف', $lang)),
  array('contact', 'mail', bdr_v15_t('Contact', 'Contact', 'اتصل بنا', $lang)),
);
$short = array('algeriens-residents-etranger' => array(bdr_v15_t('Résidents à l’étranger', 'Residents abroad', 'المقيمون بالخارج', $lang)));
$langs = array('ar' => array('AR', 'العربية'), 'fr' => array('FR', 'Français'), 'en' => array('EN', 'English'));
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); echo bdr_v15_sprite(); ?>
<a class="skip-link" href="#main"><?php echo esc_html(bdr_v15_t('Aller au contenu', 'Skip to content', 'انتقل إلى المحتوى', $lang)); ?></a>

<header class="site-header" id="site-header">
  <div class="container hdr-grid">
    <a class="brand" href="<?php echo esc_url(bdr_v11_url('', $lang)); ?>" aria-label="BDR — <?php echo esc_attr($ui['home']); ?>">
      <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-bdr.png'); ?>" srcset="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-bdr-sm.png'); ?> 280w, <?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-bdr.png'); ?> 560w" sizes="110px" width="560" height="397" alt="<?php echo esc_attr(bdr_v15_t('BDR – Banque de Développement Régional', 'BDR – Regional Development Bank', 'BDR – بنك التنمية الجهوية', $lang)); ?>" fetchpriority="high">
    </a>

    <div class="hdr-util">
      <div class="util-contact">
        <a href="tel:<?php echo esc_attr($phone_raw); ?>"><?php echo bdr_v15_icon('phone'); ?><span dir="ltr"><?php echo esc_html($phone); ?></span></a>
        <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo bdr_v15_icon('mail'); ?><span><?php echo esc_html($email); ?></span></a>
      </div>
      <nav class="util-links" aria-label="<?php echo esc_attr(bdr_v15_t('Accès rapides', 'Quick access', 'وصول سريع', $lang)); ?>">
        <?php foreach ($shortcuts as $s): if ($s[0] === 'banque-en-ligne') continue; ?>
          <a href="<?php echo esc_url(bdr_v11_url($s[0], $lang)); ?>"<?php echo $cur === $s[0] ? ' aria-current="page"' : ''; ?>><?php echo bdr_v15_icon($s[1]); ?><span><?php echo esc_html($s[2]); ?></span></a>
        <?php endforeach; ?>
        <a class="util-open" href="<?php echo esc_url(bdr_v11_url('ouvrir-un-compte', $lang)); ?>"><?php echo esc_html($ui['open']); ?></a>
      </nav>
      <div class="util-tools">
        <button class="icon-btn" id="bdr-search-open" type="button" aria-haspopup="dialog" aria-label="<?php echo esc_attr(bdr_v15_t('Rechercher dans le site', 'Search the site', 'بحث في الموقع', $lang)); ?>"><?php echo bdr_v15_icon('search'); ?></button>
        <span class="lang-switch" role="group" aria-label="<?php echo esc_attr(bdr_v15_t('Langue', 'Language', 'اللغة', $lang)); ?>">
          <?php foreach ($langs as $lc => $info): ?>
            <a href="<?php echo esc_url(bdr_v15_switch_url($lc)); ?>" lang="<?php echo esc_attr($lc); ?>" hreflang="<?php echo esc_attr($lc); ?>" title="<?php echo esc_attr($info[1]); ?>"<?php echo $lc === $lang ? ' aria-current="true" class="is-active"' : ''; ?>><?php echo esc_html($info[0]); ?></a>
          <?php endforeach; ?>
        </span>
      </div>
    </div>

    <?php // Extension BDR-NET active : le bouton mène à l'espace client BDR-NET (/fr|en|ar/bdr-net/).
    $ec_url = function_exists('bdr_net_space_url') ? bdr_net_space_url($lang) : bdr_v11_url('banque-en-ligne', $lang);
    $ec_cur = function_exists('bdr_net_is_space_page') ? bdr_net_is_space_page() : $cur === 'banque-en-ligne'; ?>
    <a class="espace-client" href="<?php echo esc_url($ec_url); ?>"<?php echo $ec_cur ? ' aria-current="page"' : ''; ?>>
      <?php echo bdr_v15_icon('lock', 'ic ic-lg'); ?>
      <span class="ec-text"><strong><?php echo esc_html(bdr_v15_t('Espace client', 'Customer area', 'فضاء العميل', $lang)); ?></strong><small><?php echo esc_html(bdr_v15_t('Connexion BDR-NET', 'Sign in to BDR-NET', 'دخول BDR-NET', $lang)); ?></small></span>
    </a>

    <div class="hdr-tools">
      <button class="icon-btn mobile-search" id="bdr-search-open-m" type="button" aria-haspopup="dialog" aria-label="<?php echo esc_attr(bdr_v15_t('Rechercher dans le site', 'Search the site', 'بحث في الموقع', $lang)); ?>"><?php echo bdr_v15_icon('search', 'ic ic-lg'); ?></button>
      <button class="icon-btn nav-burger" id="bdr-burger" type="button" aria-expanded="false" aria-controls="bdr-drawer" aria-label="<?php echo esc_attr(bdr_v15_t('Ouvrir le menu', 'Open the menu', 'فتح القائمة', $lang)); ?>"><?php echo bdr_v15_icon('menu', 'ic ic-lg'); ?></button>
    </div>

  <nav class="main-nav" aria-label="<?php echo esc_attr(bdr_v15_t('Navigation principale', 'Main navigation', 'التنقل الرئيسي', $lang)); ?>">
    <ul class="container nav-list">
      <?php foreach (bdr_v15_nav() as $top):
        $ts = $top['slug']; $has = !empty($top['cols']); $is_cur = ($active_top === $ts); ?>
        <li class="nav-item<?php echo $has ? ' has-mega' : ''; ?><?php echo $is_cur ? ' is-current' : ''; ?>">
          <a class="nav-link" href="<?php echo esc_url(bdr_v11_url($ts, $lang)); ?>"<?php echo $is_cur ? ' aria-current="true"' : ''; ?>><?php echo esc_html(isset($short[$ts]) ? $short[$ts][0] : bdr_v11_title($ts, $lang)); ?></a>
          <?php if ($has): ?>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="mega-<?php echo esc_attr($ts); ?>"><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?><span class="sr-only"><?php echo esc_html(bdr_v15_t('Afficher le sous-menu', 'Show submenu', 'عرض القائمة الفرعية', $lang) . ' — ' . bdr_v11_title($ts, $lang)); ?></span></button>
            <div class="mega<?php echo !empty($top['small']) ? ' mega-small' : ''; ?>" id="mega-<?php echo esc_attr($ts); ?>">
              <div class="container mega-in">
                <div class="mega-cols cols-<?php echo count($top['cols']); ?>">
                  <?php foreach ($top['cols'] as $col):
                    $head_slug = is_string($col['head']) ? $col['head'] : (isset($col['head_slug']) ? $col['head_slug'] : $ts); ?>
                    <div class="mega-col">
                      <a class="mega-head" href="<?php echo esc_url(bdr_v11_url($head_slug, $lang)); ?>"><?php echo bdr_v15_icon(bdr_v15_icon_for($head_slug)); ?><span><?php echo esc_html(bdr_v15_head_label($col['head'], $lang)); ?></span></a>
                      <?php bdr_v15_render_items($col['items'], $lang); ?>
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (!empty($top['foot'])): ?>
                  <div class="mega-foot">
                    <?php foreach ($top['foot'] as $fs): ?>
                      <a href="<?php echo esc_url(bdr_v11_url($fs, $lang)); ?>"><?php echo bdr_v15_icon(bdr_v15_icon_for($fs)); ?><?php echo esc_html(bdr_v11_title($fs, $lang)); ?><?php echo bdr_v15_icon('arrow', 'ic ic-arrow'); ?></a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
  </div>
</header>

<div class="drawer-backdrop" id="bdr-drawer-backdrop" hidden></div>
<aside class="drawer" id="bdr-drawer" aria-label="<?php echo esc_attr(bdr_v15_t('Menu', 'Menu', 'القائمة', $lang)); ?>" hidden>
  <div class="drawer-top">
    <strong><?php echo esc_html(bdr_v15_t('Menu', 'Menu', 'القائمة', $lang)); ?></strong>
    <button class="icon-btn" id="bdr-drawer-close" type="button" aria-label="<?php echo esc_attr(bdr_v15_t('Fermer le menu', 'Close the menu', 'إغلاق القائمة', $lang)); ?>"><?php echo bdr_v15_icon('close', 'ic ic-lg'); ?></button>
  </div>
  <div class="drawer-body">
    <?php foreach (bdr_v15_nav() as $top): $ts = $top['slug']; ?>
      <?php if (empty($top['cols'])): ?>
        <a class="drawer-link" href="<?php echo esc_url(bdr_v11_url($ts, $lang)); ?>"><?php echo bdr_v15_icon(bdr_v15_icon_for($ts)); ?><?php echo esc_html(bdr_v11_title($ts, $lang)); ?></a>
      <?php else: ?>
        <details class="drawer-group"<?php echo $active_top === $ts ? ' open' : ''; ?>>
          <summary><?php echo bdr_v15_icon(bdr_v15_icon_for($ts)); ?><span><?php echo esc_html(bdr_v11_title($ts, $lang)); ?></span><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?></summary>
          <div class="drawer-sub">
            <a class="drawer-all" href="<?php echo esc_url(bdr_v11_url($ts, $lang)); ?>"><?php echo esc_html($ui['all']); ?> — <?php echo esc_html(bdr_v11_title($ts, $lang)); ?></a>
            <?php foreach ($top['cols'] as $col):
              $head_slug = is_string($col['head']) ? $col['head'] : (isset($col['head_slug']) ? $col['head_slug'] : $ts); ?>
              <div class="drawer-col">
                <a class="drawer-head" href="<?php echo esc_url(bdr_v11_url($head_slug, $lang)); ?>"><?php echo esc_html(bdr_v15_head_label($col['head'], $lang)); ?></a>
                <?php bdr_v15_render_items($col['items'], $lang); ?>
              </div>
            <?php endforeach; ?>
            <?php if (!empty($top['foot'])): foreach ($top['foot'] as $fs): ?>
              <a class="drawer-all" href="<?php echo esc_url(bdr_v11_url($fs, $lang)); ?>"><?php echo esc_html(bdr_v11_title($fs, $lang)); ?></a>
            <?php endforeach; endif; ?>
          </div>
        </details>
      <?php endif; ?>
    <?php endforeach; ?>
    <div class="drawer-quick">
      <?php foreach ($shortcuts as $s): ?>
        <a href="<?php echo esc_url(bdr_v11_url($s[0], $lang)); ?>"><?php echo bdr_v15_icon($s[1]); ?><span><?php echo esc_html($s[2]); ?></span></a>
      <?php endforeach; ?>
    </div>
    <a class="btn btn-primary btn-block" href="<?php echo esc_url(bdr_v11_url('ouvrir-un-compte', $lang)); ?>"><?php echo esc_html($ui['open']); ?></a>
  </div>
</aside>
