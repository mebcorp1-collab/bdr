<?php
get_header();
$lang = bdr_v11_lang();
$u = bdr_v11_ui($lang);
?>
<main id="main" class="site-main" tabindex="-1">
<?php while (have_posts()) : the_post();
  $id = get_the_ID();
  $lat = get_post_meta($id, '_bdr_lat', true); $lng = get_post_meta($id, '_bdr_lng', true);
  $rows = array(
    array(bdr_v15_t('Code agence', 'Branch code', 'رمز الوكالة', $lang), get_post_meta($id, '_bdr_code', true)),
    array(bdr_v15_t('Adresse', 'Address', 'العنوان', $lang), get_post_meta($id, '_bdr_adresse', true)),
    array(bdr_v15_t('Ville', 'City', 'المدينة', $lang), get_post_meta($id, '_bdr_ville', true)),
    array('Wilaya', get_post_meta($id, '_bdr_wilaya', true)),
    array(bdr_v15_t('Téléphone', 'Phone', 'الهاتف', $lang), get_post_meta($id, '_bdr_tel', true)),
  );
?>
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy">
    <div class="eyebrow"><?php echo esc_html(bdr_v15_t('Réseau BDR', 'BDR network', 'شبكة BDR', $lang)); ?></div>
    <h1><?php the_title(); ?></h1>
    <div class="hero-actions"><a class="btn btn-gold" href="<?php echo esc_url(bdr_v11_url('agences', $lang)); ?>"><?php echo bdr_v15_icon('pin'); ?> <?php echo esc_html(bdr_v15_t('Toutes les agences', 'All branches', 'جميع الوكالات', $lang)); ?></a></div>
  </div></div></section>
  <section class="section"><div class="container article-layout">
    <div class="panel">
      <h2><?php echo esc_html(bdr_v15_t('Coordonnées', 'Contact details', 'بيانات الاتصال', $lang)); ?></h2>
      <dl class="agency-dl">
        <?php foreach ($rows as $r) : if ($r[1] === '' || $r[1] === null) continue; ?>
          <dt><?php echo esc_html($r[0]); ?></dt><dd><?php echo esc_html($r[1]); ?></dd>
        <?php endforeach; ?>
      </dl>
      <?php if ($lat !== '' && $lng !== '') : ?>
        <p><a class="btn btn-primary" target="_blank" rel="noopener" href="<?php echo esc_url('https://www.openstreetmap.org/?mlat=' . rawurlencode($lat) . '&mlon=' . rawurlencode($lng) . '#map=16/' . rawurlencode($lat) . '/' . rawurlencode($lng)); ?>"><?php echo bdr_v15_icon('pin'); ?> <?php echo esc_html(bdr_v15_t('Voir sur OpenStreetMap', 'View on OpenStreetMap', 'عرض على OpenStreetMap', $lang)); ?></a></p>
      <?php endif; ?>
    </div>
    <aside class="panel side-panel"><h2><?php echo esc_html($u['contact']); ?></h2><p><?php echo esc_html(bdr_v15_t('Une question ? Écrivez à la BDR ou contactez directement l’agence.', 'A question? Write to BDR or contact the branch directly.', 'لديك سؤال؟ راسل BDR أو اتصل بالوكالة مباشرة.', $lang)); ?></p><a class="btn btn-outline btn-block" href="<?php echo esc_url(bdr_v11_url('contact', $lang)); ?>"><?php echo esc_html($u['contact']); ?></a></aside>
  </div></section>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
