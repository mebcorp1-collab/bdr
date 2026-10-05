<?php
// Gabarit de secours (WordPress l'exige). Les pages du site passent par page.php et front-page.php.
get_header();
$lang = bdr_v11_lang();
?>
<main id="main" class="site-main" tabindex="-1">
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy">
    <h1><?php echo esc_html(is_search() ? bdr_v15_t('Résultats de recherche', 'Search results', 'نتائج البحث', $lang) : get_bloginfo('name')); ?></h1>
  </div></div></section>
  <section class="section"><div class="container">
    <?php if (have_posts()) : ?>
      <div class="news-grid">
        <?php while (have_posts()) : the_post(); ?>
          <article class="news-card"><a href="<?php echo esc_url(get_permalink()); ?>"><div class="news-card-body">
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(bdr_v15_date(get_post_time('U'), $lang)); ?></time>
            <h3><?php the_title(); ?></h3>
            <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 26, '…')); ?></p>
          </div></a></article>
        <?php endwhile; ?>
      </div>
      <?php the_posts_pagination(); ?>
    <?php else : ?>
      <div class="panel"><p><?php echo esc_html(bdr_v15_t('Aucun contenu trouvé.', 'Nothing found.', 'لم يتم العثور على محتوى.', $lang)); ?></p></div>
    <?php endif; ?>
  </div></section>
</main>
<?php get_footer(); ?>
