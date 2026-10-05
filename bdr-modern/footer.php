<?php
$lang = bdr_v11_lang();
$ui = bdr_v11_ui($lang);
$phone = bdr_v15_opt('phone'); $phone_raw = bdr_v15_opt('phone_raw'); $email = bdr_v15_opt('email');
$socials = array('facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'instagram' => 'Instagram');
$docs = array(
  'conditions-generales-particulier.pdf'         => bdr_v15_t('Conditions générales — Particuliers', 'General terms — Individuals', 'الشروط العامة — الأفراد', $lang),
  'conditions-generales-entreprise.pdf'          => bdr_v15_t('Conditions générales — Entreprises', 'General terms — Businesses', 'الشروط العامة — المؤسسات', $lang),
  'conditions-generales-finance-islamique.pdf'   => bdr_v15_t('Conditions générales — Finance islamique', 'General terms — Islamic finance', 'الشروط العامة — المالية الإسلامية', $lang),
);
$cols = array(
  array(bdr_v11_title('la-banque', $lang), array('la-banque', 'gouvernance', 'engagements', 'actualites', 'recrutement', 'institutionnels')),
  array(bdr_v11_title('particuliers', $lang), array('comptes-cartes', 'cartes-bancaires', 'credits', 'epargne', 'placements', 'finance-islamique', 'algeriens-residents-etranger')),
  array(bdr_v11_title('entreprises', $lang), array('entreprises-comptes-bancaires', 'financement', 'commerce-exterieur', 'agriculture', 'carte-affaires')),
  array(bdr_v15_t('Services', 'Services', 'الخدمات', $lang), array('banque-en-ligne', 'securite-digitale', 'taux-de-change', 'agences', 'ouvrir-un-compte', 'contact')),
);
?>
<section class="cta-strip">
  <div class="container cta-strip-in">
    <div>
      <h2><?php echo esc_html(bdr_v15_t('Un projet ? Une question ? Parlons-en.', 'A project? A question? Let’s talk.', 'لديك مشروع أو سؤال؟ لنتحدث.', $lang)); ?></h2>
      <p><?php echo esc_html(bdr_v15_t('Ouvrez un compte, trouvez l’agence la plus proche ou contactez un conseiller BDR.', 'Open an account, find the nearest branch or contact a BDR adviser.', 'افتح حساباً أو اعثر على أقرب وكالة أو تواصل مع مستشار BDR.', $lang)); ?></p>
    </div>
    <div class="cta-strip-actions">
      <a class="btn btn-gold" href="<?php echo esc_url(bdr_v11_url('ouvrir-un-compte', $lang)); ?>"><?php echo bdr_v15_icon('user-plus'); ?> <?php echo esc_html($ui['open']); ?></a>
      <a class="btn btn-ghost" href="<?php echo esc_url(bdr_v11_url('agences', $lang)); ?>"><?php echo bdr_v15_icon('pin'); ?> <?php echo esc_html($ui['branches']); ?></a>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="footer-logo" href="<?php echo esc_url(bdr_v11_url('', $lang)); ?>"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-bdr-sm.png'); ?>" width="280" height="198" loading="lazy" alt="BDR"></a>
      <p class="footer-intro"><?php echo esc_html(bdr_v15_t('Banque de Développement Régional', 'Regional Development Bank', 'بنك التنمية الجهوية', $lang)); ?></p>
      <ul class="footer-contact">
        <li><a href="tel:<?php echo esc_attr($phone_raw); ?>"><?php echo bdr_v15_icon('phone'); ?><span dir="ltr"><?php echo esc_html($phone); ?></span></a></li>
        <li><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo bdr_v15_icon('mail'); ?><span><?php echo esc_html($email); ?></span></a></li>
      </ul>
      <?php $have = false; foreach ($socials as $k => $n) if (bdr_v15_opt($k)) $have = true; if ($have): ?>
        <ul class="social">
          <?php foreach ($socials as $k => $n): $u = bdr_v15_opt($k); if (!$u) continue; ?>
            <li><a href="<?php echo esc_url($u); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($n); ?>"><?php echo bdr_v15_icon($k); ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php foreach ($cols as $col): ?>
      <nav class="footer-col" aria-label="<?php echo esc_attr($col[0]); ?>">
        <h2><?php echo esc_html($col[0]); ?></h2>
        <ul>
          <?php foreach ($col[1] as $s): ?>
            <li><a href="<?php echo esc_url(bdr_v11_url($s, $lang)); ?>"><?php echo esc_html(bdr_v11_title($s, $lang)); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
    <?php endforeach; ?>
  </div>

  <?php
  $have_docs = array();
  foreach ($docs as $file => $label) if (file_exists(get_template_directory() . '/assets/documents/' . $file)) $have_docs[$file] = $label;
  if ($have_docs): ?>
    <div class="container footer-docs">
      <strong><?php echo esc_html(bdr_v15_t('Documents à télécharger', 'Documents to download', 'وثائق للتحميل', $lang)); ?></strong>
      <?php foreach ($have_docs as $file => $label): ?>
        <a href="<?php echo esc_url(get_template_directory_uri() . '/assets/documents/' . $file); ?>" target="_blank" rel="noopener"><?php echo bdr_v15_icon('download'); ?><?php echo esc_html($label); ?> <small>PDF</small></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="container copyright">
    <span>© <?php echo esc_html(date('Y')); ?> <?php echo esc_html(bdr_v15_t('Banque de Développement Régional', 'Regional Development Bank', 'بنك التنمية الجهوية', $lang)); ?></span>
    <span class="legal-links">
      <a href="<?php echo esc_url(bdr_v11_url('mentions-legales', $lang)); ?>"><?php echo esc_html(bdr_v11_title('mentions-legales', $lang)); ?></a>
      <a href="<?php echo esc_url(bdr_v11_url('donnees-personnelles', $lang)); ?>"><?php echo esc_html(bdr_v11_title('donnees-personnelles', $lang)); ?></a>
      <a href="<?php echo esc_url(bdr_v11_url('plan-du-site', $lang)); ?>"><?php echo esc_html(bdr_v11_title('plan-du-site', $lang)); ?></a>
    </span>
  </div>
</footer>

<div id="bdr-search-overlay" class="search-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr(bdr_v15_t('Recherche dans le site', 'Search the site', 'بحث في الموقع', $lang)); ?>" hidden>
  <div class="search-dialog">
    <div class="search-top">
      <?php echo bdr_v15_icon('search'); ?>
      <input id="bdr-search-input" type="search" autocomplete="off" aria-label="<?php echo esc_attr(bdr_v15_t('Rechercher', 'Search', 'بحث', $lang)); ?>" placeholder="<?php echo esc_attr(bdr_v15_t('Rechercher dans tout le site…', 'Search the whole site…', 'ابحث في الموقع…', $lang)); ?>">
      <button id="bdr-search-close" class="icon-btn" type="button" aria-label="<?php echo esc_attr(bdr_v15_t('Fermer', 'Close', 'إغلاق', $lang)); ?>"><?php echo bdr_v15_icon('close'); ?></button>
    </div>
    <div id="bdr-search-results" aria-live="polite"><div class="search-empty"><?php echo esc_html(bdr_v15_t('Commencez à saisir votre recherche…', 'Start typing to search…', 'اكتب ما تبحث عنه…', $lang)); ?></div></div>
  </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
