<?php
get_header();
$lang = bdr_v11_lang();
?>
<main id="main" class="site-main" tabindex="-1">
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy">
    <h1><?php echo esc_html(bdr_v15_t('Nos offres', 'Our offers', 'عروضنا', $lang)); ?></h1>
  </div></div></section>
  <section class="section"><div class="container">
    <?php if (have_posts()) : ?>
      <div class="news-grid">
        <?php while (have_posts()) : the_post(); ?>
          <article class="news-card"><a href="<?php echo esc_url(get_permalink()); ?>">
            <?php if (has_post_thumbnail()) echo '<div class="news-card-img">' . get_the_post_thumbnail(get_the_ID(), 'medium_large', array('loading' => 'lazy')) . '</div>'; ?>
            <div class="news-card-body"><h3><?php the_title(); ?></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 26, '…')); ?></p></div>
          </a></article>
        <?php endwhile; ?>
      </div>
    <?php else : ?>
      <div class="panel"><p><?php echo esc_html(bdr_v15_t('Aucune offre publiée pour le moment.', 'No offers published yet.', 'لا توجد عروض منشورة حالياً.', $lang)); ?></p></div>
    <?php endif; ?>
  </div></section>
</main>
<?php get_footer(); ?>
