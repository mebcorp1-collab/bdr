<?php
/**
 * Accueil — structure inspirée des sites bancaires de référence :
 * bannière à défilement, accès rapides, univers, chiffres réels, cours indicatifs, actualités.
 * Un seul <h1> (première bannière). Aucun script en ligne.
 */
get_header();
$lang = bdr_v11_lang();
$u = bdr_v11_ui($lang);
$t = function ($fr, $en, $ar) use ($lang) { return bdr_v15_t($fr, $en, $ar, $lang); };

$slides = array(
  array('img' => 'la-banque', 'kicker' => $t('Banque de Développement Régional', 'Regional Development Bank', 'بنك التنمية الجهوية'),
        'title' => $t('Au service des territoires et de vos projets', 'Serving the regions and your projects', 'في خدمة الأقاليم ومشاريعكم'),
        'text' => $t('Une banque engagée aux côtés des particuliers, des entreprises et des acteurs qui font vivre les régions.', 'A bank supporting individuals, businesses and the communities that drive regional development.', 'بنك يرافق الأفراد والمؤسسات والفاعلين الذين يساهمون في تنمية الأقاليم.'),
        'cta' => array($t('Découvrir nos solutions', 'Discover our solutions', 'اكتشفوا حلولنا'), 'particuliers'), 'cta2' => array($u['branches'], 'agences')),
  array('img' => 'credit-immobilier', 'kicker' => $t('Particuliers', 'Individuals', 'الأفراد'),
        'title' => $t('Financez votre logement et vos projets de vie', 'Finance your home and life projects', 'موّلوا سكنكم ومشاريع حياتكم'),
        'text' => $t('Crédit immobilier, consommation, automobile : comparez les solutions et rapprochez-vous d’un conseiller.', 'Home, consumer and auto loans: compare solutions and talk to an adviser.', 'تمويل عقاري واستهلاكي وسيارات: قارنوا الحلول وتواصلوا مع مستشار.'),
        'cta' => array(bdr_v11_title('credits', $lang), 'credits'), 'cta2' => array($u['open'], 'ouvrir-un-compte')),
  array('img' => 'banque-en-ligne', 'kicker' => $t('Banque en ligne', 'Online banking', 'الخدمات عبر الإنترنت'),
        'title' => $t('Vos comptes à portée de main, en toute sécurité', 'Your accounts at your fingertips, securely', 'حساباتكم في متناول يدكم وبأمان'),
        'text' => $t('Connectez-vous à BDR-NET, suivez vos alertes SMS et appliquez les bons réflexes de sécurité.', 'Sign in to BDR-NET, follow your SMS alerts and apply good security habits.', 'سجّلوا الدخول إلى BDR-NET وتابعوا تنبيهات SMS وطبّقوا ممارسات الأمان.'),
        'cta' => array($t('Espace client', 'Customer area', 'فضاء العميل'), 'banque-en-ligne'), 'cta2' => array(bdr_v11_title('securite-digitale', $lang), 'securite-digitale')),
  array('img' => 'financement-agriculture', 'kicker' => $t('Agriculture & territoires', 'Agriculture & regions', 'الفلاحة والأقاليم'),
        'title' => $t('Investir dans l’agriculture et l’industrie locale', 'Invest in agriculture and local industry', 'استثمروا في الفلاحة والصناعة المحلية'),
        'text' => $t('Des financements dédiés aux exploitants, aux industriels et aux acteurs de la pêche et de l’aquaculture.', 'Financing dedicated to farmers, manufacturers and fishing and aquaculture operators.', 'تمويلات موجهة للفلاحين والصناعيين وأنشطة الصيد وتربية المائيات.'),
        'cta' => array(bdr_v11_title('agriculture', $lang), 'agriculture'), 'cta2' => array(bdr_v11_title('financement', $lang), 'financement')),
  array('img' => 'commerce-exterieur', 'kicker' => $t('Commerce extérieur', 'International trade', 'التجارة الخارجية'),
        'title' => $t('Sécurisez vos opérations à l’international', 'Secure your international transactions', 'أمّنوا عملياتكم الدولية'),
        'text' => $t('Domiciliation, crédit documentaire, remise documentaire, transferts et garanties internationales.', 'Domiciliation, documentary credit, documentary collection, transfers and international guarantees.', 'التوطين والاعتماد المستندي والتحصيل المستندي والتحويلات والضمانات الدولية.'),
        'cta' => array(bdr_v11_title('commerce-exterieur', $lang), 'commerce-exterieur'), 'cta2' => array($u['contact'], 'contact')),
  array('img' => 'finance-islamique', 'kicker' => $t('Finance islamique', 'Islamic finance', 'التمويل الإسلامي'),
        'title' => $t('Des financements conformes à vos principes', 'Financing in line with your principles', 'تمويلات تتوافق مع مبادئكم'),
        'text' => $t('Mourabaha, Ijara et comptes dédiés, pour les particuliers comme pour les entreprises.', 'Mourabaha, Ijara and dedicated accounts, for individuals and businesses alike.', 'المرابحة والإجارة وحسابات مخصصة للأفراد والمؤسسات.'),
        'cta' => array(bdr_v11_title('finance-islamique', $lang), 'finance-islamique'), 'cta2' => array($u['open'], 'ouvrir-un-compte')),
);

$quick = array(
  array('ouvrir-un-compte', 'user-plus', $t('Devenir client', 'Become a client', 'كن عميلاً')),
  array('credit-immobilier', 'home', $t('Crédit immobilier', 'Home loan', 'قرض عقاري')),
  array('commerce-exterieur', 'ship', $t('Commerce extérieur', 'International trade', 'التجارة الخارجية')),
  array('finance-islamique', 'crescent', $t('Finance islamique', 'Islamic finance', 'التمويل الإسلامي')),
  array('financement-agriculture', 'wheat', $t('Financement agricole', 'Agri financing', 'التمويل الفلاحي')),
  array('cartes-bancaires', 'card', $t('Cartes bancaires', 'Bank cards', 'البطاقات البنكية')),
  array('epargne', 'coins', $t('Épargne', 'Savings', 'الادخار')),
  array('taux-de-change', 'exchange', $t('Taux de change', 'Exchange rates', 'أسعار الصرف')),
);

$universes = array(
  array('particuliers', 'particuliers', $t('Comptes, cartes, crédits, épargne et services du quotidien.', 'Accounts, cards, loans, savings and everyday services.', 'الحسابات والبطاقات والتمويل والادخار والخدمات اليومية.')),
  array('entreprises', 'entreprises', $t('Financement, gestion des flux et commerce extérieur pour votre activité.', 'Financing, cash management and international trade for your business.', 'التمويل وإدارة التدفقات والتجارة الخارجية لنشاطكم.')),
  array('finance-islamique', 'finance-islamique', $t('Mourabaha, Ijara et comptes conformes aux principes de la finance islamique.', 'Mourabaha, Ijara and accounts in line with Islamic finance principles.', 'المرابحة والإجارة وحسابات متوافقة مع مبادئ التمويل الإسلامي.')),
  array('agriculture', 'agriculture', $t('Des solutions pour les investissements agricoles, industriels et la pêche.', 'Solutions for agricultural, industrial and fishing investments.', 'حلول للاستثمارات الفلاحية والصناعية وقطاع الصيد.')),
  array('banque-en-ligne', 'banque-en-ligne', $t('Services à distance, alertes SMS et conseils de sécurité digitale.', 'Remote services, SMS alerts and digital security advice.', 'خدمات عن بعد وتنبيهات SMS ونصائح الأمان الرقمي.')),
  array('algeriens-residents-etranger', 'algeriens-residents-etranger', $t('Des services pour vos projets et vos opérations en Algérie.', 'Services for your projects and transactions in Algeria.', 'خدمات لمشاريعكم وعملياتكم في الجزائر.')),
);

$stats = bdr_v15_network_stats();
$fx_all = bdr_v15_fx_rows(); $fx = array_slice($fx_all, 0, 6); $fx_set = bdr_v16_fx_dataset();
$fx_two = true; foreach ($fx as $r0) { if ($r0['buy'] === '' || $r0['sell'] === '') $fx_two = false; }
$panos = bdr_v16_panoramas($lang);
$fx_date = bdr_v15_fx_date($lang);
?>
<main id="main" class="site-main home" tabindex="-1">

<section class="hero" id="bdr-hero" aria-roledescription="carousel" aria-label="<?php echo esc_attr($t('À la une', 'Highlights', 'أبرز الخدمات')); ?>">
  <div class="hero-track">
    <?php foreach ($slides as $i => $s): $img = bdr_v15_image($s['img']); $first = ($i === 0); ?>
      <article class="hero-slide<?php echo $first ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr(($i + 1) . ' / ' . count($slides)); ?>"<?php echo $first ? '' : ' aria-hidden="true"'; ?>>
        <div class="hero-panel">
          <div class="hero-copy">
            <p class="hero-kicker"><?php echo esc_html($s['kicker']); ?></p>
            <?php echo $first ? '<h1>' : '<h2 class="hero-title">'; echo esc_html($s['title']); echo $first ? '</h1>' : '</h2>'; ?>
            <p class="hero-text"><?php echo esc_html($s['text']); ?></p>
            <div class="hero-actions">
              <a class="btn btn-gold" href="<?php echo esc_url(bdr_v11_url($s['cta'][1], $lang)); ?>"<?php echo $first ? '' : ' tabindex="-1"'; ?>><?php echo esc_html($s['cta'][0]); ?> <?php echo bdr_v15_icon('arrow', 'ic ic-arrow'); ?></a>
              <a class="btn btn-ghost" href="<?php echo esc_url(bdr_v11_url($s['cta2'][1], $lang)); ?>"<?php echo $first ? '' : ' tabindex="-1"'; ?>><?php echo esc_html($s['cta2'][0]); ?></a>
            </div>
          </div>
        </div>
        <div class="hero-frame"><?php echo bdr_v15_img_tag($img, bdr_v16_photo_alt($img['photo'], $lang), $first, '(min-width: 900px) 55vw, 100vw', 'hero-img'); ?></div>
      </article>
    <?php endforeach; ?>
  </div>
  <div class="hero-controls">
    <div class="hero-dots">
      <?php foreach ($slides as $i => $s): ?>
        <button class="hero-dot<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button" data-go="<?php echo (int)$i; ?>" aria-label="<?php echo esc_attr($t('Aller à la bannière', 'Go to slide', 'الانتقال إلى الشريحة') . ' ' . ($i + 1)); ?>"<?php echo $i === 0 ? ' aria-current="true"' : ''; ?>></button>
      <?php endforeach; ?>
    </div>
    <div class="hero-btns">
      <button class="hero-btn hero-prev" type="button" aria-label="<?php echo esc_attr($t('Bannière précédente', 'Previous slide', 'الشريحة السابقة')); ?>"><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?></button>
      <button class="hero-btn hero-next" type="button" aria-label="<?php echo esc_attr($t('Bannière suivante', 'Next slide', 'الشريحة التالية')); ?>"><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?></button>
      <button class="hero-btn hero-play" type="button" data-state="playing" aria-label="<?php echo esc_attr($t('Mettre en pause le défilement', 'Pause automatic slides', 'إيقاف التمرير التلقائي')); ?>"><?php echo bdr_v15_icon('pause'); ?></button>
    </div>
  </div>
</section>

<nav class="quick-band" aria-label="<?php echo esc_attr($t('Accès rapides', 'Quick access', 'وصول سريع')); ?>">
  <ul class="quick-grid">
    <?php foreach ($quick as $q): ?>
      <li><a href="<?php echo esc_url(bdr_v11_url($q[0], $lang)); ?>"><?php echo bdr_v15_icon($q[1], 'ic ic-lg'); ?><span class="quick-label"><?php echo esc_html($q[2]); ?></span></a></li>
    <?php endforeach; ?>
  </ul>
</nav>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2><?php echo esc_html($t('Une banque pour chaque projet', 'A bank for every project', 'بنك لكل مشروع')); ?></h2>
        <p class="section-intro"><?php echo esc_html($t('Explorez les solutions de la Banque de Développement Régional selon votre profil.', 'Explore the solutions of the Regional Development Bank according to your profile.', 'اكتشفوا حلول بنك التنمية الجهوية حسب ملفكم.')); ?></p>
      </div>
    </div>
    <div class="universe-grid">
      <?php foreach ($universes as $un): $img = bdr_v15_image($un[1]); ?>
        <a class="universe-card" href="<?php echo esc_url(bdr_v11_url($un[0], $lang)); ?>">
          <span class="universe-img"><?php echo bdr_v15_img_tag($img, '', false, '(min-width: 900px) 40vw, 100vw'); ?></span>
          <span class="universe-body">
            <span class="pc-icon"><?php echo bdr_v15_icon(bdr_v15_icon_for($un[0])); ?></span>
            <span class="universe-text">
              <h3><?php echo esc_html(bdr_v11_title($un[0], $lang)); ?></h3>
              <span class="universe-desc"><?php echo esc_html($un[2]); ?></span>
            </span>
            <span class="universe-go"><?php echo bdr_v15_icon('arrow', 'ic ic-arrow'); ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="pano-section" id="bdr-pano" aria-label="<?php echo esc_attr($t('Galerie panoramique', 'Panoramic gallery', 'معرض بانورامي')); ?>">
  <div class="container pano-head">
    <div>
      <h2><?php echo esc_html($t('Partout où se construisent les projets', 'Wherever projects take shape', 'حيثما تُبنى المشاريع')); ?></h2>
      <p><?php echo esc_html($t('Du littoral au Sud, des villes aux campagnes : faites défiler les panoramas.', 'From the coast to the South, from cities to the countryside: scroll through the panoramas.', 'من الساحل إلى الجنوب، ومن المدن إلى الأرياف: تصفّحوا البانوراميات.')); ?></p>
    </div>
    <div class="pano-nav">
      <span class="pano-count" id="bdr-pano-count" aria-live="polite">1 / <?php echo count($panos); ?></span>
      <button class="pano-btn pano-prev" type="button" aria-label="<?php echo esc_attr($t('Panorama précédent', 'Previous panorama', 'البانوراما السابقة')); ?>"><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?></button>
      <button class="pano-btn pano-next" type="button" aria-label="<?php echo esc_attr($t('Panorama suivant', 'Next panorama', 'البانوراما التالية')); ?>"><?php echo bdr_v15_icon('chevron', 'ic ic-chev'); ?></button>
    </div>
  </div>
  <div class="pano-rail" id="bdr-pano-rail" tabindex="0" role="region" aria-label="<?php echo esc_attr($t('Panoramas défilants', 'Scrolling panoramas', 'بانوراميات قابلة للتمرير')); ?>">
    <?php foreach ($panos as $pi => $pn): ?>
      <figure class="pano">
        <img src="<?php echo esc_url($pn['src']); ?>" srcset="<?php echo esc_url($pn['src_s']); ?> 800w, <?php echo esc_url($pn['src']); ?> 1600w" sizes="(min-width: 1200px) 1120px, 88vw" width="1600" height="500" alt="<?php echo esc_attr($pn['title']); ?>" decoding="async"<?php echo $pi === 0 ? '' : ' loading="lazy"'; ?>>
        <figcaption><strong><?php echo esc_html($pn['title']); ?></strong><span><?php echo esc_html($pn['text']); ?></span></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>

<section class="section figures-section">
  <div class="container figures">
    <div class="figures-left">
      <h2><?php echo esc_html($t('La BDR en repères', 'BDR at a glance', 'BDR في أرقام')); ?></h2>
      <?php if (!empty($stats['agencies'])): ?>
      <dl class="facts">
        <div><dt><?php echo esc_html($t('agences sur le territoire', 'branches nationwide', 'وكالة عبر التراب الوطني')); ?></dt><dd><?php echo esc_html(number_format_i18n($stats['agencies'])); ?></dd></div>
        <?php if (!empty($stats['wilayas'])): ?><div><dt><?php echo esc_html($t('wilayas couvertes', 'wilayas covered', 'ولاية مغطاة')); ?></dt><dd><?php echo esc_html(number_format_i18n($stats['wilayas'])); ?></dd></div><?php endif; ?>
        <div><dt><?php echo esc_html($t('langues du site', 'site languages', 'لغات الموقع')); ?></dt><dd><?php echo esc_html($t('AR, FR, EN', 'AR, FR, EN', 'عربي، فرنسي، إنجليزي')); ?></dd></div>
      </dl>
      <?php endif; ?>
      <a class="btn btn-primary" href="<?php echo esc_url(bdr_v11_url('agences', $lang)); ?>"><?php echo bdr_v15_icon('pin'); ?> <?php echo esc_html($u['branches']); ?></a>
    </div>
    <div class="figures-right">
      <div class="fx-head">
        <h2><?php echo esc_html($t('Cours du dinar', 'Dinar exchange rates', 'أسعار صرف الدينار')); ?></h2>
        <?php if ($fx_date): ?><p class="fx-date"><?php echo esc_html($t('Cours du', 'Rates of', 'أسعار يوم')); ?> <time><?php echo esc_html($fx_date); ?></time></p><?php endif; ?>
      </div>
      <table class="fx-board">
        <thead><tr><th scope="col"><?php echo esc_html($t('Devise', 'Currency', 'العملة')); ?></th><?php if ($fx_two): ?><th scope="col" class="num"><?php echo esc_html($t('Achat', 'Buy', 'شراء')); ?></th><th scope="col" class="num"><?php echo esc_html($t('Vente', 'Sell', 'بيع')); ?></th><?php else: ?><th scope="col" class="num"><?php echo esc_html($t('Cours en DA', 'Rate in DZD', 'السعر بالدينار')); ?></th><?php endif; ?></tr></thead>
        <tbody>
          <?php foreach ($fx as $r): ?>
            <tr><th scope="row"><b><?php echo esc_html($r['code']); ?></b> <span><?php echo esc_html($r['names'][$lang]); ?></span></th>
              <?php if ($fx_two): ?><td class="num" dir="ltr"><?php echo esc_html($r['buy']); ?></td><td class="num" dir="ltr"><?php echo esc_html($r['sell']); ?></td><?php else: ?><td class="num" dir="ltr"><?php echo esc_html($r['value']); ?></td><?php endif; ?></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="fx-source"><?php echo esc_html($fx_set['auto']
        ? $t('Source : Banque d’Algérie, actualisé automatiquement chaque jour. Cours indicatifs.', 'Source: Bank of Algeria, updated automatically every day. Indicative rates.', 'المصدر: بنك الجزائر، يُحدَّث تلقائياً كل يوم. أسعار إرشادية.')
        : $t('Cours saisis par la BDR à titre indicatif.', 'Rates entered by BDR for information only.', 'أسعار أدخلها BDR للإعلام فقط.')); ?>
        <a class="link" href="<?php echo esc_url(bdr_v11_url('taux-de-change', $lang)); ?>"><?php echo esc_html($t('Toutes les devises', 'All currencies', 'جميع العملات')); ?> <?php echo bdr_v15_icon('arrow', 'ic ic-arrow'); ?></a></p>
    </div>
  </div>
</section>

<section class="section news-section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2><?php echo esc_html($t('Les informations de la BDR', 'BDR information', 'معلومات BDR')); ?></h2>
      </div>
      <a class="link-more" href="<?php echo esc_url(bdr_v11_url('actualites', $lang)); ?>"><?php echo esc_html($t('Toutes les actualités', 'All news', 'جميع الأخبار')); ?> <?php echo bdr_v15_icon('arrow', 'ic ic-arrow'); ?></a>
    </div>
    <?php bdr_v15_render_news($lang, 3); ?>
  </div>
</section>

</main>
<?php get_footer(); ?>
