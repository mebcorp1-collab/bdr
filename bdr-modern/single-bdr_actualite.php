<?php
get_header();
$lang = bdr_v11_lang();
$u = bdr_v11_ui($lang);
$parent = bdr_v11_url('actualites', $lang);
?>
<nav class="bdr-breadcrumbs" aria-label="<?php echo esc_attr(bdr_v15_t('Fil d’Ariane', 'Breadcrumb', 'مسار التنقل', $lang)); ?>"><div class="container">
  <a href="<?php echo esc_url(bdr_v11_url('', $lang)); ?>"><?php echo esc_html($u['home']); ?></a><span>›</span>
  <a href="<?php echo esc_url($parent); ?>"><?php echo esc_html(bdr_v11_title('actualites', $lang)); ?></a><span>›</span>
  <strong><?php the_title(); ?></strong>
</div></nav>
<main id="main" class="site-main" tabindex="-1">
  <?php while (have_posts()) : the_post(); ?>
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy">
    <div class="eyebrow"><?php echo esc_html(bdr_v15_t('Actualité', 'News', 'خبر', $lang)); ?> · <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(bdr_v15_date(get_post_time('U'), $lang)); ?></time></div>
    <h1><?php the_title(); ?></h1>
  </div></div></section>
  <section class="section"><div class="container article-layout">
    <article class="prose panel news-article">
      <?php if (has_post_thumbnail()) : ?><div class="news-article-cover"><?php the_post_thumbnail('large', array('loading' => 'eager', 'fetchpriority' => 'high', 'alt' => esc_attr(get_the_title()))); ?></div><?php endif; ?>
      <?php the_content(); ?>
      <div class="article-actions">
        <a class="btn btn-outline" href="<?php echo esc_url($parent); ?>"><?php echo bdr_v15_icon('arrow', 'ic ic-arrow ic-back'); ?> <?php echo esc_html(bdr_v15_t('Toutes les actualités', 'All news', 'جميع الأخبار', $lang)); ?></a>
        <a class="btn btn-primary" href="<?php echo esc_url(bdr_v11_url('contact', $lang)); ?>"><?php echo esc_html($u['contact']); ?></a>
      </div>
    </article>
    <aside class="panel side-panel">
      <h2><?php echo esc_html(bdr_v15_t('Continuer la visite', 'Keep exploring', 'تابع الزيارة', $lang)); ?></h2>
      <p><?php echo esc_html(bdr_v15_t('Retrouvez les solutions de la BDR pour les particuliers et les entreprises.', 'Discover BDR solutions for individuals and businesses.', 'اكتشف حلول BDR للأفراد والمؤسسات.', $lang)); ?></p>
      <p><a class="btn btn-primary btn-block" href="<?php echo esc_url(bdr_v11_url('particuliers', $lang)); ?>"><?php echo esc_html(bdr_v11_title('particuliers', $lang)); ?></a></p>
      <p><a class="btn btn-outline btn-block" href="<?php echo esc_url(bdr_v11_url('entreprises', $lang)); ?>"><?php echo esc_html(bdr_v11_title('entreprises', $lang)); ?></a></p>
    </aside>
  </div></section>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
