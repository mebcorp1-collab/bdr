<?php
get_header();
$lang = bdr_v11_lang();
$u = bdr_v11_ui($lang);
?>
<main id="main" class="site-main" tabindex="-1">
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy">
    <div class="eyebrow">404</div>
    <h1><?php echo esc_html(bdr_v15_t('Page introuvable', 'Page not found', 'الصفحة غير موجودة', $lang)); ?></h1>
    <p><?php echo esc_html(bdr_v15_t('Le lien est peut-être ancien ou erroné. Utilisez la recherche ou l’un des accès ci-dessous.', 'The link may be outdated or mistyped. Use search or one of the shortcuts below.', 'ربما يكون الرابط قديماً أو خاطئاً. استخدم البحث أو أحد الروابط أدناه.', $lang)); ?></p>
  </div></div></section>
  <section class="section"><div class="container">
    <?php bdr_related(array(
      array($u['home'], bdr_v11_url('', $lang), bdr_v15_t('Revenir à l’accueil.', 'Back to the home page.', 'العودة إلى الصفحة الرئيسية.', $lang)),
      array($u['branches'], bdr_v11_url('agences', $lang), bdr_v15_t('Consulter le réseau d’agences.', 'Browse the branch network.', 'استعرض شبكة الوكالات.', $lang)),
      array($u['contact'], bdr_v11_url('contact', $lang), bdr_v15_t('Écrire à la BDR.', 'Write to BDR.', 'راسل BDR.', $lang)),
    )); ?>
  </div></section>
</main>
<?php get_footer(); ?>
