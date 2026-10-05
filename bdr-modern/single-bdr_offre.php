<?php
get_header();
$lang = bdr_v11_lang();
?>
<main id="main" class="site-main" tabindex="-1">
<?php while (have_posts()) : the_post(); ?>
  <section class="page-hero page-hero-plain"><div class="container page-hero-inner"><div class="page-hero-copy"><h1><?php the_title(); ?></h1></div></div></section>
  <section class="section"><div class="container"><article class="prose panel">
    <?php if (has_post_thumbnail()) echo '<div class="news-article-cover">' . get_the_post_thumbnail(get_the_ID(), 'large') . '</div>'; ?>
    <?php the_content(); ?>
  </article></div></section>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
