<?php
if (!defined('ABSPATH')) exit;

/**
 * BDR V11.3 – multilingual routing/content layer.
 * Visual layout deliberately reuses the V10 templates/CSS.
 */

function bdr_v11_lang(){
  $lang = get_query_var('bdr_lang');
  if (in_array($lang, array('fr','en','ar'), true)) return $lang;
  if (is_front_page()) return 'ar';
  return 'fr';
}
function bdr_v11_slug(){
  $slug = get_query_var('bdr_slug');
  if ($slug) return sanitize_title($slug);
  return is_front_page() ? '' : sanitize_title(get_post_field('post_name', get_queried_object_id()));
}
function bdr_v11_url($slug='', $lang=null){
  $lang = $lang ?: bdr_v11_lang();
  $slug = trim((string)$slug, '/');
  if ($slug === '') {
    if ($lang === 'ar') return home_url('/');
    return home_url('/'.$lang.'/');
  }
  return home_url('/'.$lang.'/'.$slug.'/');
}


function bdr_v11_parent_slug($slug){
  static $parents=null;
  if ($parents===null) {
    $parents=array(
      'gouvernance'=>'la-banque','engagements'=>'la-banque','particuliers'=>'',
      'comptes-bancaires'=>'comptes-cartes','comptes-cartes'=>'particuliers','compte-cheque'=>'comptes-bancaires','operations-quotidiennes'=>'compte-cheque','versements-retraits'=>'operations-quotidiennes','compte-devise'=>'comptes-bancaires',
      'cartes-bancaires'=>'comptes-cartes','carte-cib'=>'cartes-bancaires','carte-visa'=>'cartes-bancaires','carte-rahati'=>'cartes-bancaires','carte-internationale'=>'cartes-bancaires',
      'credits'=>'particuliers','credit-immobilier'=>'credits','credit-consommation'=>'credits','credit-auto'=>'credits',
      'epargne'=>'particuliers','epargne-disponible'=>'epargne','epargne-projet'=>'epargne','epargne-jeune'=>'epargne','placements'=>'epargne','depot-terme'=>'placements','bons-caisse'=>'placements',
      'entreprises'=>'','entreprises-comptes-bancaires'=>'entreprises','carte-affaires'=>'entreprises','placements-entreprises'=>'entreprises','financement'=>'entreprises','financement-investissement'=>'financement','financement-exploitation'=>'financement','financement-pme-pmi'=>'financement',
      'commerce-exterieur'=>'entreprises','domiciliation'=>'commerce-exterieur','credit-documentaire'=>'commerce-exterieur','remise-documentaire'=>'commerce-exterieur','garanties-internationales'=>'commerce-exterieur','transfert-libre'=>'commerce-exterieur',
      'agriculture'=>'entreprises','financement-agriculture'=>'agriculture','financement-industrie'=>'agriculture','financement-peche-aquaculture'=>'agriculture',
      'finance-islamique'=>'particuliers','finance-islamique-comptes'=>'finance-islamique','livrets-epargne'=>'epargne','mourabaha'=>'finance-islamique','mourabaha-consommation'=>'mourabaha','mourabaha-travaux'=>'mourabaha','mourabaha-equipements'=>'mourabaha','mourabaha-agriculture'=>'mourabaha','ijara'=>'finance-islamique','ijara-materiel-roulant'=>'ijara','ijara-medical'=>'ijara','ijara-travaux-publics'=>'ijara',
      'banque-en-ligne'=>'particuliers','services-distance'=>'banque-en-ligne','alertes-sms'=>'banque-en-ligne','location-coffre'=>'banque-en-ligne','securite-digitale'=>'banque-en-ligne',
      'algeriens-residents-etranger'=>'particuliers','institutionnels'=>'la-banque','recrutement'=>'la-banque','taux-de-change'=>'la-banque','agences'=>'la-banque','actualites'=>'la-banque','ouvrir-un-compte'=>'particuliers','contact'=>'la-banque',
      'mentions-legales'=>'','donnees-personnelles'=>'','plan-du-site'=>'',
    );
  }
  return $parents[$slug] ?? '';
}

function bdr_v11_catalog(){
  static $c=null; if($c!==null) return $c;
  $c=array(
    ''=>array('fr'=>'Accueil','en'=>'Home','ar'=>'الرئيسية','ey_fr'=>'BANQUE DE DÉVELOPPEMENT RÉGIONAL','ey_en'=>'REGIONAL DEVELOPMENT BANK','ey_ar'=>'بنك التنمية الجهوية'),
    'la-banque'=>array('fr'=>'La Banque','en'=>'The Bank','ar'=>'البنك','ey_fr'=>'LA BANQUE','ey_en'=>'THE BANK','ey_ar'=>'البنك'),
    'gouvernance'=>array('fr'=>'Gouvernance','en'=>'Governance','ar'=>'الحوكمة','ey_fr'=>'GOUVERNANCE','ey_en'=>'GOVERNANCE','ey_ar'=>'الحوكمة'),
    'engagements'=>array('fr'=>'Nos engagements','en'=>'Our commitments','ar'=>'التزاماتنا','ey_fr'=>'ENGAGEMENTS','ey_en'=>'COMMITMENTS','ey_ar'=>'التزاماتنا'),
    'particuliers'=>array('fr'=>'Particuliers','en'=>'Individuals','ar'=>'الأفراد','ey_fr'=>'PARTICULIERS','ey_en'=>'INDIVIDUALS','ey_ar'=>'الأفراد'),
    'comptes-bancaires'=>array('fr'=>'Comptes bancaires','en'=>'Bank accounts','ar'=>'الحسابات البنكية','ey_fr'=>'PARTICULIERS · COMPTES','ey_en'=>'INDIVIDUALS · ACCOUNTS','ey_ar'=>'الأفراد · الحسابات'),
    'comptes-cartes'=>array('fr'=>'Comptes & Cartes','en'=>'Accounts & Cards','ar'=>'الحسابات والبطاقات','ey_fr'=>'PARTICULIERS','ey_en'=>'INDIVIDUALS','ey_ar'=>'الأفراد'),
    'compte-cheque'=>array('fr'=>'Compte courant','en'=>'Current account','ar'=>'الحساب الجاري','ey_fr'=>'PARTICULIERS · COMPTE','ey_en'=>'INDIVIDUALS · ACCOUNT','ey_ar'=>'الأفراد · الحساب'),
    'operations-quotidiennes'=>array('fr'=>'Opérations quotidiennes','en'=>'Everyday banking','ar'=>'العمليات اليومية','ey_fr'=>'COMPTE COURANT','ey_en'=>'CURRENT ACCOUNT','ey_ar'=>'الحساب الجاري'),
    'versements-retraits'=>array('fr'=>'Versements et retraits','en'=>'Deposits and withdrawals','ar'=>'الإيداعات والسحوبات','ey_fr'=>'COMPTE COURANT · OPÉRATIONS','ey_en'=>'CURRENT ACCOUNT · TRANSACTIONS','ey_ar'=>'الحساب الجاري · العمليات'),
    'compte-devise'=>array('fr'=>'Compte en devises','en'=>'Foreign currency account','ar'=>'حساب بالعملة الأجنبية','ey_fr'=>'PARTICULIERS · DEVISES','ey_en'=>'INDIVIDUALS · CURRENCY','ey_ar'=>'الأفراد · العملات'),
    'cartes-bancaires'=>array('fr'=>'Cartes bancaires','en'=>'Bank cards','ar'=>'البطاقات البنكية','ey_fr'=>'PARTICULIERS · MOYENS DE PAIEMENT','ey_en'=>'INDIVIDUALS · PAYMENT METHODS','ey_ar'=>'الأفراد · وسائل الدفع'),
    'carte-cib'=>array('fr'=>'Carte CIB','en'=>'CIB Card','ar'=>'بطاقة CIB','ey_fr'=>'PARTICULIERS · CARTE','ey_en'=>'INDIVIDUALS · CARD','ey_ar'=>'الأفراد · البطاقة'),
    'carte-rahati'=>array('fr'=>'Carte Rahati','en'=>'Rahati Card','ar'=>'بطاقة راحتي','ey_fr'=>'PARTICULIERS · CARTE','ey_en'=>'INDIVIDUALS · CARD','ey_ar'=>'الأفراد · البطاقة'),
    'carte-internationale'=>array('fr'=>'Carte internationale','en'=>'International card','ar'=>'بطاقة دولية','ey_fr'=>'PARTICULIERS · CARTE','ey_en'=>'INDIVIDUALS · CARD','ey_ar'=>'الأفراد · البطاقة'),
    'carte-visa'=>array('fr'=>'Carte Visa','en'=>'Visa Card','ar'=>'بطاقة فيزا','ey_fr'=>'PARTICULIERS · CARTE','ey_en'=>'INDIVIDUALS · CARD','ey_ar'=>'الأفراد · البطاقة'),
    'credits'=>array('fr'=>'Crédits','en'=>'Loans','ar'=>'التمويلات','ey_fr'=>'PARTICULIERS · FINANCEMENT','ey_en'=>'INDIVIDUALS · FINANCING','ey_ar'=>'الأفراد · التمويل'),
    'credit-immobilier'=>array('fr'=>'Crédit immobilier','en'=>'Home loan','ar'=>'التمويل العقاري','ey_fr'=>'PARTICULIERS · FINANCEMENT','ey_en'=>'INDIVIDUALS · FINANCING','ey_ar'=>'الأفراد · التمويل'),
    'credit-consommation'=>array('fr'=>'Crédit à la consommation','en'=>'Consumer loan','ar'=>'قرض استهلاكي','ey_fr'=>'PARTICULIERS · FINANCEMENT','ey_en'=>'INDIVIDUALS · FINANCING','ey_ar'=>'الأفراد · التمويل'),
    'credit-auto'=>array('fr'=>'Crédit automobile','en'=>'Auto loan','ar'=>'تمويل السيارات','ey_fr'=>'PARTICULIERS · FINANCEMENT','ey_en'=>'INDIVIDUALS · FINANCING','ey_ar'=>'الأفراد · التمويل'),
    'epargne'=>array('fr'=>'Épargne','en'=>'Savings','ar'=>'الادخار','ey_fr'=>'PARTICULIERS · ÉPARGNE','ey_en'=>'INDIVIDUALS · SAVINGS','ey_ar'=>'الأفراد · الادخار'),
    'epargne-disponible'=>array('fr'=>'Épargne disponible','en'=>'Accessible savings','ar'=>'ادخار متاح','ey_fr'=>'PARTICULIERS · ÉPARGNE','ey_en'=>'INDIVIDUALS · SAVINGS','ey_ar'=>'الأفراد · الادخار'),
    'epargne-projet'=>array('fr'=>'Épargne projet','en'=>'Goal-based savings','ar'=>'ادخار للمشاريع','ey_fr'=>'PARTICULIERS · ÉPARGNE','ey_en'=>'INDIVIDUALS · SAVINGS','ey_ar'=>'الأفراد · الادخار'),
    'epargne-jeune'=>array('fr'=>'Épargne jeune','en'=>'Youth savings','ar'=>'ادخار الشباب','ey_fr'=>'PARTICULIERS · ÉPARGNE','ey_en'=>'INDIVIDUALS · SAVINGS','ey_ar'=>'الأفراد · الادخار'),
    'placements'=>array('fr'=>'Placements','en'=>'Investments','ar'=>'الاستثمارات','ey_fr'=>'PARTICULIERS · PLACEMENTS','ey_en'=>'INDIVIDUALS · INVESTMENTS','ey_ar'=>'الأفراد · الاستثمارات'),
    'depot-terme'=>array('fr'=>'Dépôt à terme','en'=>'Term deposit','ar'=>'الوديعة لأجل','ey_fr'=>'PARTICULIERS · PLACEMENT','ey_en'=>'INDIVIDUALS · INVESTMENT','ey_ar'=>'الأفراد · الاستثمار'),
    'bons-caisse'=>array('fr'=>'Bons de caisse','en'=>'Cash certificates','ar'=>'سندات الصندوق','ey_fr'=>'PARTICULIERS · PLACEMENT','ey_en'=>'INDIVIDUALS · INVESTMENT','ey_ar'=>'الأفراد · الاستثمار'),
    'entreprises'=>array('fr'=>'Entreprises','en'=>'Businesses','ar'=>'المؤسسات والشركات','ey_fr'=>'ENTREPRISES','ey_en'=>'BUSINESSES','ey_ar'=>'المؤسسات والشركات'),
    'entreprises-comptes-bancaires'=>array('fr'=>'Comptes entreprises','en'=>'Business accounts','ar'=>'حسابات الشركات','ey_fr'=>'ENTREPRISES · COMPTES','ey_en'=>'BUSINESSES · ACCOUNTS','ey_ar'=>'المؤسسات · الحسابات'),
    'carte-affaires'=>array('fr'=>'Carte affaires','en'=>'Business card','ar'=>'بطاقة الأعمال','ey_fr'=>'ENTREPRISES · CARTE','ey_en'=>'BUSINESSES · CARD','ey_ar'=>'المؤسسات · البطاقة'),
    'placements-entreprises'=>array('fr'=>'Placements entreprises','en'=>'Business investments','ar'=>'استثمارات الشركات','ey_fr'=>'ENTREPRISES · PLACEMENTS','ey_en'=>'BUSINESSES · INVESTMENTS','ey_ar'=>'المؤسسات · الاستثمارات'),
    'financement'=>array('fr'=>'Financement','en'=>'Financing','ar'=>'التمويل','ey_fr'=>'ENTREPRISES · FINANCEMENT','ey_en'=>'BUSINESSES · FINANCING','ey_ar'=>'المؤسسات · التمويل'),
    'financement-investissement'=>array('fr'=>'Financement investissement','en'=>'Investment financing','ar'=>'تمويل الاستثمار','ey_fr'=>'ENTREPRISES · FINANCEMENT','ey_en'=>'BUSINESSES · FINANCING','ey_ar'=>'المؤسسات · التمويل'),
    'financement-exploitation'=>array('fr'=>'Financement exploitation','en'=>'Working-capital financing','ar'=>'تمويل الاستغلال','ey_fr'=>'ENTREPRISES · FINANCEMENT','ey_en'=>'BUSINESSES · FINANCING','ey_ar'=>'المؤسسات · التمويل'),
    'financement-pme-pmi'=>array('fr'=>'Financement PME / PMI','en'=>'SME financing','ar'=>'تمويل المؤسسات الصغيرة والمتوسطة','ey_fr'=>'ENTREPRISES · PME','ey_en'=>'BUSINESSES · SME','ey_ar'=>'المؤسسات · المؤسسات الصغيرة والمتوسطة'),
    'commerce-exterieur'=>array('fr'=>'Commerce extérieur','en'=>'International trade','ar'=>'التجارة الخارجية','ey_fr'=>'ENTREPRISES · COMMERCE EXTÉRIEUR','ey_en'=>'BUSINESSES · INTERNATIONAL TRADE','ey_ar'=>'المؤسسات · التجارة الخارجية'),
    'domiciliation'=>array('fr'=>'Domiciliation','en'=>'Trade domiciliation','ar'=>'التوطين البنكي','ey_fr'=>'COMMERCE EXTÉRIEUR','ey_en'=>'INTERNATIONAL TRADE','ey_ar'=>'التجارة الخارجية'),
    'credit-documentaire'=>array('fr'=>'Crédit documentaire','en'=>'Documentary credit','ar'=>'الاعتماد المستندي','ey_fr'=>'COMMERCE EXTÉRIEUR','ey_en'=>'INTERNATIONAL TRADE','ey_ar'=>'التجارة الخارجية'),
    'remise-documentaire'=>array('fr'=>'Remise documentaire','en'=>'Documentary collection','ar'=>'التحصيل المستندي','ey_fr'=>'COMMERCE EXTÉRIEUR','ey_en'=>'INTERNATIONAL TRADE','ey_ar'=>'التجارة الخارجية'),
    'garanties-internationales'=>array('fr'=>'Garanties internationales','en'=>'International guarantees','ar'=>'الضمانات الدولية','ey_fr'=>'COMMERCE EXTÉRIEUR','ey_en'=>'INTERNATIONAL TRADE','ey_ar'=>'التجارة الخارجية'),
    'transfert-libre'=>array('fr'=>'Transfert libre','en'=>'Open account transfer','ar'=>'التحويل الحر','ey_fr'=>'COMMERCE EXTÉRIEUR','ey_en'=>'INTERNATIONAL TRADE','ey_ar'=>'التجارة الخارجية'),
    'agriculture'=>array('fr'=>'Agriculture & Territoires','en'=>'Agriculture & Territories','ar'=>'الفلاحة والأقاليم','ey_fr'=>'AGRICULTURE & TERRITOIRES','ey_en'=>'AGRICULTURE & TERRITORIES','ey_ar'=>'الفلاحة والأقاليم'),
    'financement-agriculture'=>array('fr'=>'Financement agriculture','en'=>'Agricultural financing','ar'=>'تمويل الفلاحة','ey_fr'=>'AGRICULTURE','ey_en'=>'AGRICULTURE','ey_ar'=>'الفلاحة'),
    'financement-industrie'=>array('fr'=>'Financement industrie','en'=>'Industrial financing','ar'=>'تمويل الصناعة','ey_fr'=>'SECTEURS','ey_en'=>'SECTORS','ey_ar'=>'القطاعات'),
    'financement-peche-aquaculture'=>array('fr'=>'Financement pêche & aquaculture','en'=>'Fisheries & aquaculture financing','ar'=>'تمويل الصيد وتربية المائيات','ey_fr'=>'SECTEURS','ey_en'=>'SECTORS','ey_ar'=>'القطاعات'),
    'finance-islamique'=>array('fr'=>'Finance islamique','en'=>'Islamic finance','ar'=>'المالية الإسلامية','ey_fr'=>'FINANCE ISLAMIQUE','ey_en'=>'ISLAMIC FINANCE','ey_ar'=>'المالية الإسلامية'),
    'finance-islamique-comptes'=>array('fr'=>'Comptes de finance islamique','en'=>'Islamic finance accounts','ar'=>'حسابات المالية الإسلامية','ey_fr'=>'FINANCE ISLAMIQUE · COMPTES','ey_en'=>'ISLAMIC FINANCE · ACCOUNTS','ey_ar'=>'المالية الإسلامية · الحسابات'),
    'livrets-epargne'=>array('fr'=>'Livrets d’épargne','en'=>'Savings books','ar'=>'دفاتر الادخار','ey_fr'=>'FINANCE ISLAMIQUE · ÉPARGNE','ey_en'=>'ISLAMIC FINANCE · SAVINGS','ey_ar'=>'المالية الإسلامية · الادخار'),
    'mourabaha'=>array('fr'=>'Mourabaha','en'=>'Mourabaha','ar'=>'المرابحة','ey_fr'=>'FINANCE ISLAMIQUE · FINANCEMENT','ey_en'=>'ISLAMIC FINANCE · FINANCING','ey_ar'=>'المالية الإسلامية · التمويل'),
    'mourabaha-consommation'=>array('fr'=>'Mourabaha consommation','en'=>'Mourabaha consumer financing','ar'=>'مرابحة استهلاكية','ey_fr'=>'FINANCE ISLAMIQUE · PARTICULIERS','ey_en'=>'ISLAMIC FINANCE · INDIVIDUALS','ey_ar'=>'المالية الإسلامية · الأفراد'),
    'mourabaha-travaux'=>array('fr'=>'Mourabaha travaux','en'=>'Mourabaha home works','ar'=>'مرابحة الأشغال','ey_fr'=>'FINANCE ISLAMIQUE · HABITAT','ey_en'=>'ISLAMIC FINANCE · HOME','ey_ar'=>'المالية الإسلامية · السكن'),
    'mourabaha-equipements'=>array('fr'=>'Mourabaha équipements professionnels','en'=>'Mourabaha professional equipment','ar'=>'مرابحة التجهيزات المهنية','ey_fr'=>'FINANCE ISLAMIQUE · ENTREPRISES','ey_en'=>'ISLAMIC FINANCE · BUSINESSES','ey_ar'=>'المالية الإسلامية · المؤسسات'),
    'mourabaha-agriculture'=>array('fr'=>'Mourabaha production agricole','en'=>'Mourabaha agricultural production','ar'=>'مرابحة الإنتاج الفلاحي','ey_fr'=>'FINANCE ISLAMIQUE · AGRICULTURE','ey_en'=>'ISLAMIC FINANCE · AGRICULTURE','ey_ar'=>'المالية الإسلامية · الفلاحة'),
    'ijara'=>array('fr'=>'Ijara','en'=>'Ijara','ar'=>'الإجارة','ey_fr'=>'FINANCE ISLAMIQUE · FINANCEMENT','ey_en'=>'ISLAMIC FINANCE · FINANCING','ey_ar'=>'المالية الإسلامية · التمويل'),
    'ijara-materiel-roulant'=>array('fr'=>'Ijara matériel roulant','en'=>'Ijara rolling equipment','ar'=>'إجارة المعدات المتنقلة','ey_fr'=>'FINANCE ISLAMIQUE · ENTREPRISES','ey_en'=>'ISLAMIC FINANCE · BUSINESSES','ey_ar'=>'المالية الإسلامية · المؤسسات'),
    'ijara-medical'=>array('fr'=>'Ijara matériel médical','en'=>'Ijara medical equipment','ar'=>'إجارة المعدات الطبية','ey_fr'=>'FINANCE ISLAMIQUE · SANTÉ','ey_en'=>'ISLAMIC FINANCE · HEALTHCARE','ey_ar'=>'المالية الإسلامية · الصحة'),
    'ijara-travaux-publics'=>array('fr'=>'Ijara travaux publics','en'=>'Ijara public works equipment','ar'=>'إجارة معدات الأشغال العمومية','ey_fr'=>'FINANCE ISLAMIQUE · ENTREPRISES','ey_en'=>'ISLAMIC FINANCE · BUSINESSES','ey_ar'=>'المالية الإسلامية · المؤسسات'),
    'algeriens-residents-etranger'=>array('fr'=>'Algériens résidents à l’étranger','en'=>'Algerians living abroad','ar'=>'الجزائريون المقيمون بالخارج','ey_fr'=>'SERVICES INTERNATIONAUX','ey_en'=>'INTERNATIONAL SERVICES','ey_ar'=>'الخدمات الدولية'),
    'institutionnels'=>array('fr'=>'Institutionnels','en'=>'Institutional clients','ar'=>'العملاء المؤسساتيون','ey_fr'=>'INSTITUTIONNELS','ey_en'=>'INSTITUTIONAL','ey_ar'=>'المؤسسات'),
    'banque-en-ligne'=>array('fr'=>'Banque en ligne','en'=>'Online banking','ar'=>'الخدمات المصرفية عبر الإنترنت','ey_fr'=>'SERVICES DIGITAUX','ey_en'=>'DIGITAL SERVICES','ey_ar'=>'الخدمات الرقمية'),
    'services-distance'=>array('fr'=>'Services bancaires à distance','en'=>'Remote banking services','ar'=>'الخدمات المصرفية عن بعد','ey_fr'=>'SERVICES DIGITAUX','ey_en'=>'DIGITAL SERVICES','ey_ar'=>'الخدمات الرقمية'),
    'alertes-sms'=>array('fr'=>'Alerte SMS','en'=>'SMS alerts','ar'=>'تنبيهات الرسائل القصيرة','ey_fr'=>'SERVICES DIGITAUX','ey_en'=>'DIGITAL SERVICES','ey_ar'=>'الخدمات الرقمية'),
    'location-coffre'=>array('fr'=>'Location de coffre','en'=>'Safe deposit box','ar'=>'استئجار خزنة','ey_fr'=>'PARTICULIERS · SERVICES','ey_en'=>'INDIVIDUALS · SERVICES','ey_ar'=>'الأفراد · الخدمات'),
    'securite-digitale'=>array('fr'=>'Sécurité digitale','en'=>'Digital security','ar'=>'الأمن الرقمي','ey_fr'=>'SERVICES DIGITAUX','ey_en'=>'DIGITAL SERVICES','ey_ar'=>'الخدمات الرقمية'),
    'taux-de-change'=>array('fr'=>'Taux de change','en'=>'Exchange rates','ar'=>'أسعار الصرف','ey_fr'=>'INFORMATIONS MARCHÉ','ey_en'=>'MARKET INFORMATION','ey_ar'=>'معلومات السوق'),
    'agences'=>array('fr'=>'Agences','en'=>'Branches','ar'=>'الوكالات','ey_fr'=>'RÉSEAU BDR','ey_en'=>'BDR NETWORK','ey_ar'=>'شبكة BDR'),
    'actualites'=>array('fr'=>'Actualités','en'=>'News','ar'=>'الأخبار','ey_fr'=>'INFORMATION','ey_en'=>'INFORMATION','ey_ar'=>'معلومات'),
    'ouvrir-un-compte'=>array('fr'=>'Ouvrir un compte','en'=>'Open an account','ar'=>'فتح حساب','ey_fr'=>'DEVENIR CLIENT','ey_en'=>'BECOME A CLIENT','ey_ar'=>'كن عميلاً'),
    'contact'=>array('fr'=>'Contact','en'=>'Contact','ar'=>'اتصل بنا','ey_fr'=>'RELATION CLIENT','ey_en'=>'CUSTOMER RELATIONS','ey_ar'=>'علاقات العملاء'),
    'recrutement'=>array('fr'=>'Recrutement','en'=>'Careers','ar'=>'التوظيف','ey_fr'=>'RESSOURCES HUMAINES','ey_en'=>'CAREERS','ey_ar'=>'الموارد البشرية'),
    'mentions-legales'=>array('fr'=>'Mentions légales','en'=>'Legal notice','ar'=>'الإشعارات القانونية','ey_fr'=>'INFORMATIONS','ey_en'=>'INFORMATION','ey_ar'=>'معلومات'),
    'donnees-personnelles'=>array('fr'=>'Données personnelles','en'=>'Personal data','ar'=>'البيانات الشخصية','ey_fr'=>'INFORMATIONS','ey_en'=>'INFORMATION','ey_ar'=>'معلومات'),
    'plan-du-site'=>array('fr'=>'Plan du site','en'=>'Sitemap','ar'=>'خريطة الموقع','ey_fr'=>'INFORMATIONS','ey_en'=>'INFORMATION','ey_ar'=>'معلومات'),
  );
  foreach($c as $slug=>$row){
    $c[$slug]['intro']=array('fr'=>'Retrouvez les informations essentielles, les points d’attention et les étapes utiles pour préparer votre démarche auprès de la BDR.','en'=>'Find the key information, practical points and next steps to prepare your BDR request.','ar'=>'اكتشف المعلومات الأساسية والنقاط المهمة والخطوات اللازمة لتحضير طلبك لدى BDR.');
  }
  $intro=array(
    'la-banque'=>['fr'=>'Découvrez la vocation de la Banque de Développement Régional, son rôle, sa gouvernance et ses principaux engagements.','en'=>'Discover the role of the Regional Development Bank, its governance and its commitments.','ar'=>'اكتشف مهمة بنك التنمية الجهوية ودوره وحوكمته والتزاماته.'],
    'gouvernance'=>['fr'=>'Comprendre les grands principes d’organisation, de contrôle, de conformité et de responsabilité qui structurent la banque.','en'=>'Understand the principles of organisation, control, compliance and accountability that structure the bank.','ar'=>'تعرّف على مبادئ التنظيم والرقابة والامتثال والمسؤولية التي تؤطر البنك.'],
    'engagements'=>['fr'=>'Une approche fondée sur la proximité, la qualité de service, l’accompagnement des projets et le développement durable des territoires.','en'=>'An approach built around proximity, service quality, project support and sustainable regional development.','ar'=>'نهج يقوم على القرب وجودة الخدمة ومرافقة المشاريع والتنمية المستدامة للأقاليم.'],
    'particuliers'=>['fr'=>'Une gamme pensée pour vos opérations quotidiennes, vos moyens de paiement, vos projets de financement et vos objectifs d’épargne.','en'=>'A range designed for everyday banking, payment methods, personal projects and savings goals.','ar'=>'مجموعة حلول لاحتياجاتك اليومية ووسائل الدفع ومشاريع التمويل وأهداف الادخار.'],
    'comptes-bancaires'=>['fr'=>'Découvrez les principales catégories de comptes pour organiser vos opérations, vos flux et votre relation bancaire.','en'=>'Discover the main account categories for organising your transactions, cash flows and banking relationship.','ar'=>'اكتشف أهم أنواع الحسابات لتنظيم عملياتك وتدفقاتك وعلاقتك البنكية.'],
    'comptes-cartes'=>['fr'=>'Une vue d’ensemble des comptes et moyens de paiement à associer à vos usages quotidiens.','en'=>'An overview of accounts and payment methods to match your everyday needs.','ar'=>'نظرة شاملة على الحسابات ووسائل الدفع الملائمة لاستخداماتك اليومية.'],
    'compte-cheque'=>['fr'=>'Le compte courant pour centraliser les opérations courantes, les virements, les retraits et les moyens de paiement.','en'=>'A current account for everyday transactions, transfers, withdrawals and payment methods.','ar'=>'حساب جارٍ لتجميع العمليات اليومية والتحويلات والسحوبات ووسائل الدفع.'],
    'operations-quotidiennes'=>['fr'=>'Versements, retraits, virements, prélèvements et suivi des opérations : les principaux usages du compte au quotidien.','en'=>'Deposits, withdrawals, transfers, direct debits and transaction monitoring: the main everyday uses of an account.','ar'=>'الإيداعات والسحوبات والتحويلات والاقتطاعات ومتابعة العمليات: أهم استخدامات الحساب اليومية.'],
    'compte-devise'=>['fr'=>'Un compte destiné à certains besoins en devises, dans le respect des règles et conditions applicables.','en'=>'An account designed for certain foreign-currency needs, subject to applicable rules and conditions.','ar'=>'حساب مخصص لبعض احتياجات العملات الأجنبية وفق القواعد والشروط المعمول بها.'],
    'cartes-bancaires'=>['fr'=>'Comparez les principaux usages d’une carte bancaire : paiement, retrait, gestion et sécurité.','en'=>'Understand the main uses of a bank card: payments, cash withdrawals, management and security.','ar'=>'تعرّف على الاستخدامات الأساسية للبطاقة البنكية: الدفع والسحب والإدارة والأمان.'],
    'carte-cib'=>['fr'=>'Une carte destinée aux paiements et retraits sur les réseaux compatibles, selon les services activés.','en'=>'A card for payments and cash withdrawals on compatible networks, subject to activated services.','ar'=>'بطاقة للدفع والسحب عبر الشبكات المتوافقة وفق الخدمات المفعلة.'],
    'carte-rahati'=>['fr'=>'Découvrez les usages, la gestion et les réflexes de sécurité associés à la carte Rahati.','en'=>'Discover the uses, management and security practices associated with the Rahati Card.','ar'=>'اكتشف استخدامات وإدارة وممارسات الأمان المرتبطة ببطاقة راحتي.'],
    'carte-internationale'=>['fr'=>'Une solution destinée à certains paiements et retraits à l’international lorsque les conditions d’éligibilité sont réunies.','en'=>'A solution for certain international payments and withdrawals when eligibility requirements are met.','ar'=>'حل لبعض عمليات الدفع والسحب الدولية عند استيفاء شروط الأهلية.'],
    'carte-visa'=>['fr'=>'Découvrez les usages d’une carte Visa, les précautions avant un déplacement et les réflexes en cas de perte.','en'=>'Discover Visa card use, travel precautions and what to do if the card is lost.','ar'=>'اكتشف استخدام بطاقة فيزا والاحتياطات قبل السفر والإجراءات عند فقدان البطاقة.'],
    'credits'=>['fr'=>'Un espace pour comparer les grandes familles de financement destinées aux projets personnels.','en'=>'A dedicated space to compare the main financing solutions for personal projects.','ar'=>'فضاء لمقارنة أهم حلول التمويل المخصصة للمشاريع الشخصية.'],
    'credit-immobilier'=>['fr'=>'Une page complète pour préparer un projet d’acquisition, de construction ou de travaux et comprendre le parcours de financement.','en'=>'A detailed guide to prepare a purchase, construction or home-improvement project and understand the financing journey.','ar'=>'دليل مفصل لتحضير مشروع شراء أو بناء أو أشغال وفهم مسار التمويل.'],
    'credit-consommation'=>['fr'=>'Comprenez les éléments à réunir avant une demande de financement pour un projet personnel ou l’acquisition de certains biens.','en'=>'Understand what to prepare before applying for financing for a personal project or certain purchases.','ar'=>'تعرّف على ما يجب تحضيره قبل طلب تمويل لمشروع شخصي أو لاقتناء بعض السلع.'],
    'credit-auto'=>['fr'=>'Préparez votre projet automobile : besoin, budget, véhicule, apport éventuel et capacité de remboursement.','en'=>'Prepare your vehicle project: needs, budget, vehicle, possible down payment and repayment capacity.','ar'=>'حضّر مشروع السيارة: الحاجة والميزانية والمركبة والمساهمة المحتملة والقدرة على السداد.'],
    'epargne'=>['fr'=>'Choisissez une solution d’épargne selon votre objectif, votre horizon et le niveau de disponibilité recherché.','en'=>'Choose a savings solution based on your goal, time horizon and desired availability.','ar'=>'اختر حل الادخار حسب هدفك وأفقك الزمني ومستوى السيولة الذي تحتاجه.'],
    'epargne-disponible'=>['fr'=>'Constituez une réserve en privilégiant l’accessibilité et un fonctionnement simple adapté à votre épargne courante.','en'=>'Build a reserve with an emphasis on accessibility and simple day-to-day saving.','ar'=>'كوّن احتياطياً مع التركيز على السيولة وسهولة الاستخدام الملائمة لادخارك.'],
    'epargne-projet'=>['fr'=>'Une approche structurée pour préparer une dépense ou un projet identifié dans le temps.','en'=>'A structured approach to saving for a defined expense or project over time.','ar'=>'منهج منظم للادخار من أجل نفقات أو مشروع محدد على مدى زمني.'],
    'epargne-jeune'=>['fr'=>'Des repères pour accompagner les premiers projets et habitudes d’épargne des jeunes.','en'=>'Practical guidance for building young people’s first savings habits and goals.','ar'=>'إرشادات لمرافقة الشباب في بناء عادات الادخار ومشاريعهم الأولى.'],
    'placements'=>['fr'=>'Des solutions de placement à examiner selon l’horizon, la disponibilité recherchée et les conditions contractuelles.','en'=>'Investment solutions to consider according to time horizon, liquidity needs and contractual terms.','ar'=>'حلول استثمارية تدرس حسب الأفق الزمني واحتياجات السيولة والشروط التعاقدية.'],
    'entreprises'=>['fr'=>'Une offre structurée pour la gestion de l’activité, les moyens de paiement, le financement, la trésorerie et le commerce extérieur.','en'=>'A structured offer covering business banking, payments, financing, treasury and international trade.','ar'=>'عرض منظم لإدارة النشاط ووسائل الدفع والتمويل والخزينة والتجارة الخارجية.'],
    'financement'=>['fr'=>'Une approche par besoin pour accompagner les investissements, l’exploitation et le développement des PME et entreprises.','en'=>'A needs-based approach covering investment, working capital and business growth.','ar'=>'مقاربة حسب الحاجة تشمل الاستثمار وتمويل الاستغلال وتنمية المؤسسات.'],
    'financement-investissement'=>['fr'=>'Préparez un dossier d’investissement autour du projet, de son coût, de son calendrier et de sa capacité de remboursement.','en'=>'Prepare an investment file around the project, cost, timeline and repayment capacity.','ar'=>'حضّر ملف الاستثمار حول المشروع وتكلفته وجدوله الزمني والقدرة على السداد.'],
    'financement-exploitation'=>['fr'=>'Comprenez comment documenter un besoin de trésorerie lié au cycle d’exploitation et aux flux de l’entreprise.','en'=>'Learn how to document working-capital needs linked to the business cycle and cash flows.','ar'=>'تعرّف على كيفية توثيق احتياجات الخزينة المرتبطة بدورة الاستغلال والتدفقات المالية.'],
    'financement-pme-pmi'=>['fr'=>'Des repères destinés aux PME et PMI pour préparer leurs besoins de croissance, d’investissement et d’exploitation.','en'=>'Guidance for SMEs preparing growth, investment and working-capital needs.','ar'=>'إرشادات للمؤسسات الصغيرة والمتوسطة لتحضير احتياجات النمو والاستثمار والاستغلال.'],
    'commerce-exterieur'=>['fr'=>'Une vue structurée des principales opérations d’importation, d’exportation, de paiement et de sécurisation documentaire.','en'=>'A structured overview of import, export, settlement and documentary security operations.','ar'=>'عرض منظم لعمليات الاستيراد والتصدير والتسوية وتأمين المستندات.'],
    'agriculture'=>['fr'=>'Des solutions pensées pour les exploitants, professionnels et projets contribuant au développement économique des territoires.','en'=>'Solutions for farmers, professionals and projects contributing to regional economic development.','ar'=>'حلول للفلاحين والمهنيين والمشاريع المساهمة في التنمية الاقتصادية للأقاليم.'],
    'finance-islamique'=>['fr'=>'Découvrez les grandes familles de solutions de finance islamique et leur logique contractuelle.','en'=>'Discover the main Islamic finance families and their contractual logic.','ar'=>'اكتشف أهم حلول المالية الإسلامية ومنطقها التعاقدي.'],
    'mourabaha'=>['fr'=>'Comprenez le mécanisme de Mourabaha, les étapes d’un financement et les documents utiles à préparer.','en'=>'Understand the Mourabaha mechanism, financing steps and the documents commonly prepared.','ar'=>'تعرّف على آلية المرابحة ومراحل التمويل والوثائق التي يمكن تحضيرها.'],
    'ijara'=>['fr'=>'Comprenez le principe d’Ijara et les éléments à étudier avant un financement locatif.','en'=>'Understand Ijara and the points to review before a leasing-based financing solution.','ar'=>'تعرّف على مبدأ الإجارة والنقاط التي يجب دراستها قبل التمويل بالإيجار.'],
    'banque-en-ligne'=>['fr'=>'Un espace dédié aux services BDR-NET, à la consultation à distance et aux bonnes pratiques de sécurité.','en'=>'An overview of BDR-NET, remote access and digital security best practices.','ar'=>'فضاء مخصص لخدمات BDR-NET والوصول عن بعد وممارسات الأمن الرقمي.'],
    'securite-digitale'=>['fr'=>'Les réflexes essentiels pour protéger vos identifiants et vos opérations contre les tentatives de fraude.','en'=>'Essential practices to protect credentials and transactions against fraud attempts.','ar'=>'الممارسات الأساسية لحماية بيانات الدخول والعمليات من محاولات الاحتيال.'],
    'algeriens-residents-etranger'=>['fr'=>'Des informations pour gérer des projets en Algérie depuis l’étranger et préparer une relation bancaire adaptée.','en'=>'Information for managing projects in Algeria from abroad and preparing an appropriate banking relationship.','ar'=>'معلومات لإدارة المشاريع في الجزائر من الخارج وتحضير علاقة بنكية مناسبة.'],
    'institutionnels'=>['fr'=>'Des solutions pour les institutions et organismes : gestion des flux, trésorerie, placements et projets structurants.','en'=>'Solutions for institutions and organisations: cash flows, treasury, investments and structured projects.','ar'=>'حلول للمؤسسات والهيئات تشمل التدفقات والخزينة والاستثمارات والمشاريع المهيكلة.'],
    'recrutement'=>['fr'=>'Découvrez les familles de métiers, les principes de recrutement et les possibilités de candidature.','en'=>'Discover job families, recruitment principles and application opportunities.','ar'=>'اكتشف مجالات المهن ومبادئ التوظيف وفرص الترشح.'],
  );
  foreach($intro as $s=>$v) { if(isset($c[$s])) $c[$s]['intro']=$v; }
  $c = array_map(function($r){ return $r; }, $c);
  return $c;
}

function bdr_v11_profile($slug,$lang){
  $c=bdr_v11_catalog();
  $r=$c[$slug] ?? $c[''];
  $title=$r[$lang] ?? $r['fr'];
  $ey=$r['ey_'.$lang] ?? $r['ey_fr'];
  $intro=$r['intro'][$lang] ?? $r['intro']['fr'];
  $group='general';
  if (preg_match('/^carte-/', $slug) || $slug==='cartes-bancaires' || $slug==='carte-affaires') $group='card';
  elseif (strpos($slug,'credit')===0 || strpos($slug,'financement')===0 || $slug==='credits' || $slug==='financement') $group='credit';
  elseif (strpos($slug,'epargne')===0 || strpos($slug,'placement')===0 || $slug==='depot-terme' || $slug==='bons-caisse') $group='saving';
  elseif (strpos($slug,'mourabaha')===0 || strpos($slug,'ijara')===0 || strpos($slug,'finance-islamique')===0 || $slug==='livrets-epargne') $group='islamic';
  elseif ($slug==='banque-en-ligne' || $slug==='services-distance' || $slug==='alertes-sms' || $slug==='securite-digitale') $group='digital';
  elseif ($slug==='commerce-exterieur' || in_array($slug,array('domiciliation','credit-documentaire','remise-documentaire','garanties-internationales','transfert-libre'),true)) $group='trade';
  elseif ($slug==='agriculture' || strpos($slug,'financement-agriculture')===0 || strpos($slug,'financement-industrie')===0 || strpos($slug,'financement-peche')===0) $group='agri';
  elseif (strpos($slug,'entreprises')===0 || strpos($slug,'placements-entreprises')===0 || $slug==='entreprises') $group='business';
  elseif (strpos($slug,'compte')===0 || $slug==='particuliers' || $slug==='comptes-cartes' || $slug==='operations-quotidiennes') $group='account';
  elseif (in_array($slug,array('la-banque','gouvernance','engagements','recrutement','institutionnels'),true)) $group='institution';
  elseif (in_array($slug,array('agences','actualites','ouvrir-un-compte','contact','taux-de-change','mentions-legales','donnees-personnelles','plan-du-site','location-coffre','algeriens-residents-etranger'),true)) $group='service';
  if($slug==='versements-retraits'){
    $special=array(
      'fr'=>array(
        array('Versement','Alimentez le compte par les canaux autorisés et conservez le justificatif de l’opération.',''),
        array('Retrait','Retirez des espèces auprès des points de service ou automates autorisés, selon les limites applicables.','/agences/'),
        array('Vérification','Contrôlez le montant, le compte concerné et le justificatif avant de terminer l’opération.','/securite-digitale/'),
        array('Suivi','Consultez régulièrement les mouvements et signalez rapidement toute anomalie.','/banque-en-ligne/')
      ),
      'en'=>array(
        array('Deposit','Fund the account through authorised channels and keep the transaction receipt.',''),
        array('Withdrawal','Withdraw cash through authorised service points or ATMs, subject to applicable limits.','/en/agences/'),
        array('Check the transaction','Verify the amount, account and receipt before completing the transaction.','/en/securite-digitale/'),
        array('Monitor activity','Review account movements regularly and report unusual activity promptly.','/en/banque-en-ligne/')
      ),
      'ar'=>array(
        array('الإيداع','قم بتغذية الحساب عبر القنوات المعتمدة واحتفظ بإيصال العملية.',''),
        array('السحب','اسحب الأموال من نقاط الخدمة أو أجهزة الصراف المعتمدة وفق الحدود المطبقة.','/ar/agences/'),
        array('التحقق','تحقق من المبلغ والحساب والإيصال قبل إتمام العملية.','/ar/securite-digitale/'),
        array('المتابعة','راجع حركات الحساب بانتظام وأبلغ سريعاً عن أي عملية غير عادية.','/ar/banque-en-ligne/')
      )
    );
    $features=$special[$lang];
    $audience=$lang==='ar'?'العملاء الذين يملكون حساباً جارياً صالحاً للعمليات المعنية.':($lang==='en'?'Customers with a current account eligible for the relevant transactions.':'Clients disposant d’un compte courant permettant les opérations concernées.');
    $docs=$lang==='ar'?'قد تُطلب وثيقة هوية وبيانات الحساب وأي مستند مرتبط بالعملية.':($lang==='en'?'An identity document, account details and any transaction-specific document may be required.':'Une pièce d’identité, les références du compte et tout justificatif lié à l’opération peuvent être demandés.');
    return array('title'=>$title,'eyebrow'=>$ey,'intro'=>$intro,'features'=>$features,'audience'=>$audience,'docs'=>$docs,'simulate'=>false,'group'=>'account');
  }

  $sets=array(
    'account'=>array(
      'fr'=>[['Fonctionnement','Les principales opérations et règles d’usage du compte.',''],['Services associés','Moyens de paiement, services à distance et options disponibles selon le produit.','/banque-en-ligne/'],['Gestion au quotidien','Suivez vos flux, vos opérations et vos documents utiles.','/operations-quotidiennes/'],['Sécurité','Adoptez les bonnes pratiques et signalez rapidement toute opération inhabituelle.','/securite-digitale/']],
      'en'=>[['How it works','Key transactions and account-use principles.',''],['Associated services','Payment methods, remote services and options available for the product.','/en/banque-en-ligne/'],['Everyday management','Monitor transactions, cash flows and useful documents.','/en/operations-quotidiennes/'],['Security','Use good security practices and report unusual activity quickly.','/en/securite-digitale/']],
      'ar'=>[['طريقة العمل','أهم العمليات ومبادئ استخدام الحساب.',''],['الخدمات المرتبطة','وسائل الدفع والخدمات عن بعد والخيارات المتاحة حسب المنتج.','/ar/banque-en-ligne/'],['الإدارة اليومية','تابع عملياتك وتدفقاتك ووثائقك المهمة.','/ar/operations-quotidiennes/'],['الأمان','اعتمد ممارسات الأمان الجيدة وأبلغ سريعاً عن أي عملية غير عادية.','/ar/securite-digitale/']],
    ),
    'card'=>array(
      'fr'=>[['Fonctionnalités','Paiement, retrait et services associés selon la carte et les options activées.',''],['Utilisation','Comprenez les usages en Algérie et, lorsque prévu, à l’international.',''],['Sécurité','Protégez carte, code et informations sensibles et vérifiez les opérations.','/securite-digitale/'],['Assistance','En cas de perte, de vol ou d’opération inhabituelle, utilisez immédiatement un canal officiel.','/contact/']],
      'en'=>[['Features','Payments, cash withdrawals and associated services according to the card.',''],['Usage','Understand domestic and, where applicable, international use.',''],['Security','Protect the card, PIN and sensitive information and monitor transactions.','/en/securite-digitale/'],['Assistance','In case of loss, theft or unusual activity, promptly use an official channel.','/en/contact/']],
      'ar'=>[['المزايا','الدفع والسحب والخدمات المرتبطة حسب نوع البطاقة والخيارات المفعلة.',''],['الاستخدام','فهم الاستخدام داخل الجزائر وعند الاقتضاء على المستوى الدولي.',''],['الأمان','احمِ البطاقة والرمز والبيانات الحساسة وتابع العمليات.','/ar/securite-digitale/'],['المساعدة','في حال الفقدان أو السرقة أو وجود عملية غير عادية استخدم قناة رسمية بسرعة.','/ar/contact/']],
    ),
    'credit'=>array(
      'fr'=>[['Le projet','Définissez le besoin, le montant à financer, le calendrier et les justificatifs associés.',''],['Montant & durée','Les conditions dépendent du produit, du dossier et de la capacité de remboursement.',''],['Étude du dossier','La banque analyse la situation, le projet, les charges, les revenus et les garanties éventuelles.',''],['Remboursement','La mensualité et le coût global dépendent notamment du montant, de la durée et des conditions contractuelles.','']],
      'en'=>[['The project','Define the need, amount to finance, timeline and supporting documents.',''],['Amount & term','Conditions depend on the product, file and repayment capacity.',''],['Application review','The bank reviews the customer profile, project, income, expenses and possible collateral.',''],['Repayment','Monthly payments and overall cost depend on the amount, term and contractual conditions.','']],
      'ar'=>[['المشروع','حدد الحاجة والمبلغ المطلوب تمويله والجدول الزمني والوثائق الداعمة.',''],['المبلغ والمدة','تختلف الشروط حسب المنتج والملف والقدرة على السداد.',''],['دراسة الملف','يدرس البنك وضعية العميل والمشروع والدخل والمصاريف والضمانات المحتملة.',''],['السداد','تتأثر الأقساط والتكلفة الإجمالية بالمبلغ والمدة والشروط التعاقدية.','']],
    ),
    'saving'=>array(
      'fr'=>[['Objectif','Déterminez le projet ou la réserve que vous souhaitez constituer.',''],['Disponibilité','Comparez le niveau d’accessibilité des fonds avec votre horizon.',''],['Horizon','Une durée plus longue peut modifier le choix du support et sa logique de placement.',''],['Suivi','Consultez les conditions, relevés et modalités de fonctionnement avant de souscrire.','']],
      'en'=>[['Goal','Define the project or reserve you want to build.',''],['Liquidity','Compare the accessibility of funds with your time horizon.',''],['Time horizon','A longer horizon may influence the appropriate savings or investment solution.',''],['Monitoring','Review terms, statements and operating rules before subscribing.','']],
      'ar'=>[['الهدف','حدد المشروع أو الاحتياطي الذي ترغب في تكوينه.',''],['السيولة','قارن إمكانية الوصول إلى الأموال مع أفقك الزمني.',''],['الأفق الزمني','قد يؤثر الأفق الأطول على اختيار حل الادخار أو الاستثمار المناسب.',''],['المتابعة','راجع الشروط والكشوف وقواعد التشغيل قبل الاشتراك.','']],
    ),
    'business'=>array(
      'fr'=>[['Gestion de l’activité','Comptes, flux, paiements et services bancaires pour le fonctionnement courant.','/entreprises-comptes-bancaires/'],['Investissement','Préparez les besoins liés aux équipements, à l’extension et au développement.','/financement-investissement/'],['Exploitation','Documentez les besoins de trésorerie et les décalages du cycle d’exploitation.','/financement-exploitation/'],['International','Accompagnez les importations, exportations et instruments documentaires.','/commerce-exterieur/']],
      'en'=>[['Business management','Accounts, cash flows, payments and banking services for day-to-day operations.','/en/entreprises-comptes-bancaires/'],['Investment','Prepare needs linked to equipment, expansion and business development.','/en/financement-investissement/'],['Working capital','Document cash-flow needs and timing gaps in the operating cycle.','/en/financement-exploitation/'],['International','Support import, export and documentary trade operations.','/en/commerce-exterieur/']],
      'ar'=>[['إدارة النشاط','الحسابات والتدفقات والمدفوعات والخدمات البنكية للنشاط اليومي.','/ar/entreprises-comptes-bancaires/'],['الاستثمار','تحضير احتياجات التجهيز والتوسعة وتطوير النشاط.','/ar/financement-investissement/'],['الاستغلال','توثيق احتياجات الخزينة والفوارق الزمنية في دورة الاستغلال.','/ar/financement-exploitation/'],['الدولي','مرافقة عمليات الاستيراد والتصدير والأدوات المستندية.','/ar/commerce-exterieur/']],
    ),
    'trade'=>array(
      'fr'=>[['Importation','Préparez le contrat commercial, les documents et les modalités de règlement.','/domiciliation/'],['Exportation','Organisez les documents, les flux et les modalités de paiement avec votre partenaire.',''],['Instruments documentaires','Analysez les mécanismes permettant de sécuriser une opération internationale.','/credit-documentaire/'],['Garanties','Lorsque nécessaire, examinez les garanties et les obligations documentaires.','/garanties-internationales/']],
      'en'=>[['Import','Prepare commercial documents and agreed settlement arrangements.','/en/domiciliation/'],['Export','Organise documents, cash flows and payment arrangements with your partner.',''],['Documentary instruments','Review mechanisms used to structure and secure international transactions.','/en/credit-documentaire/'],['Guarantees','Where relevant, assess guarantees and documentary obligations.','/en/garanties-internationales/']],
      'ar'=>[['الاستيراد','حضّر العقد التجاري والوثائق وكيفية التسوية المتفق عليها.','/ar/domiciliation/'],['التصدير','نظم الوثائق والتدفقات وكيفية الدفع مع شريكك.',''],['الأدوات المستندية','اطلع على الآليات المستخدمة لتنظيم وتأمين العمليات الدولية.','/ar/credit-documentaire/'],['الضمانات','عند الحاجة، ادرس الضمانات والالتزامات المستندية.','/ar/garanties-internationales/']],
    ),
    'agri'=>array(
      'fr'=>[['Projet agricole','Décrivez l’activité, le besoin, le calendrier et les dépenses prévues.',''],['Investissement','Documentez équipements, infrastructures ou modernisation de l’exploitation.','/financement-agriculture/'],['Cycle d’exploitation','Tenez compte des achats, des stocks et du calendrier de production.','/financement-exploitation/'],['Territoires','Reliez le projet à son potentiel d’activité, d’emploi et de valeur locale.','']],
      'en'=>[['Agricultural project','Describe the activity, need, timeline and planned expenses.',''],['Investment','Document equipment, infrastructure or farm modernisation needs.','/en/financement-agriculture/'],['Operating cycle','Consider purchases, stocks and production timing.','/en/financement-exploitation/'],['Territories','Explain the project’s contribution to local activity, jobs and value creation.','']],
      'ar'=>[['المشروع الفلاحي','صف النشاط والحاجة والجدول الزمني والنفقات المتوقعة.',''],['الاستثمار','وثّق حاجات التجهيز والبنية التحتية وتحديث الاستغلالية.','/ar/financement-agriculture/'],['دورة الاستغلال','خذ في الاعتبار المشتريات والمخزونات ودورة الإنتاج.','/ar/financement-exploitation/'],['الأقاليم','بيّن مساهمة المشروع في النشاط وفرص العمل وخلق القيمة محلياً.','']],
    ),
    'islamic'=>array(
      'fr'=>[['Principes','Comprenez la logique contractuelle et les conditions propres aux solutions proposées.',''],['Mourabaha','Une opération structurée autour de l’acquisition puis de la revente d’un actif selon le contrat.','/mourabaha/'],['Ijara','Un financement locatif organisé selon les modalités contractuelles applicables.','/ijara/'],['Documentation','Les conditions, actifs concernés, marges, loyers et obligations sont définis par le contrat.','']],
      'en'=>[['Principles','Understand the contractual logic and product-specific conditions.',''],['Mourabaha','A structure based on acquisition and resale of an asset under the agreed contract.','/en/mourabaha/'],['Ijara','A lease-based financing structure governed by the applicable contract terms.','/en/ijara/'],['Documentation','Terms, assets, margins, rents and obligations are defined contractually.','']],
      'ar'=>[['المبادئ','تعرّف على المنطق التعاقدي والشروط الخاصة بحلول المالية الإسلامية.',''],['المرابحة','عملية تقوم على اقتناء أصل ثم إعادة بيعه وفق العقد المتفق عليه.','/ar/mourabaha/'],['الإجارة','تمويل إيجاري منظم وفق الشروط التعاقدية المعمول بها.','/ar/ijara/'],['الوثائق','تحدد الشروط والأصول والهوامش والأجور والالتزامات بموجب العقد.','']],
    ),
    'digital'=>array(
      'fr'=>[['Accès','Utilisez uniquement les canaux officiels et protégez vos identifiants.',''],['Fonctionnalités','Consultez les services disponibles sur votre parcours digital.',''],['Sécurité','Ne partagez jamais mot de passe, PIN ou code OTP.','/securite-digitale/'],['Assistance','En cas de doute, utilisez un canal officiel pour obtenir de l’aide.','/contact/']],
      'en'=>[['Access','Use official channels only and keep your credentials private.',''],['Features','Review the digital services available for your access.',''],['Security','Never share a password, PIN or one-time code.','/en/securite-digitale/'],['Support','When in doubt, use an official channel to seek assistance.','/en/contact/']],
      'ar'=>[['الدخول','استخدم القنوات الرسمية فقط وحافظ على سرية بيانات الدخول.',''],['الوظائف','اطلع على الخدمات الرقمية المتاحة ضمن مسارك.',''],['الأمان','لا تشارك أبداً كلمة المرور أو الرقم السري أو رمز التحقق.','/ar/securite-digitale/'],['المساعدة','عند الشك استخدم قناة رسمية لطلب المساعدة.','/ar/contact/']],
    ),
    'institution'=>array(
      'fr'=>[['Mission et rôle','Comprendre la place de la banque et les publics accompagnés.',''],['Organisation','Gouvernance, responsabilités et contrôles structurent l’action de l’institution.','/gouvernance/'],['Engagements','Proximité, qualité de service et développement des territoires.','/engagements/'],['Actualités','Suivez les informations et publications institutionnelles.','/actualites/']],
      'en'=>[['Mission and role','Understand the bank’s role and the communities it supports.',''],['Organisation','Governance, responsibilities and controls structure the institution.','/en/gouvernance/'],['Commitments','Proximity, service quality and regional development.','/en/engagements/'],['News','Follow institutional information and publications.','/en/actualites/']],
      'ar'=>[['المهمة والدور','تعرّف على دور البنك والفئات التي يرافقها.',''],['التنظيم','تشكل الحوكمة والمسؤوليات والرقابة أساس عمل المؤسسة.','/ar/gouvernance/'],['الالتزامات','القرب وجودة الخدمة وتنمية الأقاليم.','/ar/engagements/'],['الأخبار','تابع المعلومات والمنشورات المؤسساتية.','/ar/actualites/']],
    ),
    'service'=>array(
      'fr'=>[['Information','Accédez aux informations utiles avant d’engager une démarche.',''],['Parcours','Choisissez le canal adapté : en ligne, téléphone, e-mail ou agence.','/contact/'],['Proximité','Consultez le réseau et les coordonnées disponibles.','/agences/'],['Sécurité','N’utilisez jamais un formulaire général pour transmettre des données d’authentification.','/securite-digitale/']],
      'en'=>[['Information','Access useful information before starting a request.',''],['Journey','Choose the appropriate channel: online, phone, email or branch.','/en/contact/'],['Proximity','Browse the available branch network and contact details.','/en/agences/'],['Security','Never use a general form to send banking credentials or security codes.','/en/securite-digitale/']],
      'ar'=>[['المعلومات','اطلع على المعلومات المفيدة قبل بدء أي إجراء.',''],['المسار','اختر القناة المناسبة: الإنترنت أو الهاتف أو البريد أو الوكالة.','/ar/contact/'],['القرب','اطلع على شبكة الوكالات وبيانات الاتصال المتاحة.','/ar/agences/'],['الأمان','لا تستخدم نموذجاً عاماً لإرسال بيانات الدخول البنكية أو رموز الأمان.','/ar/securite-digitale/']],
    ),
    'general'=>array(
      'fr'=>[['Présentation','Comprenez le rôle de cette rubrique et ses principaux usages.',''],['Caractéristiques','Retrouvez les informations et conditions à examiner avant toute démarche.',''],['Préparer votre demande','Rassemblez les éléments utiles à l’étude de votre situation.',''],['Accompagnement','Un conseiller peut vous orienter vers le parcours ou le produit adapté.','/contact/']],
      'en'=>[['Overview','Understand the purpose of this section and its main uses.',''],['Key points','Review the information and conditions to consider before starting.',''],['Prepare your request','Gather the information needed to review your situation.',''],['Support','A BDR adviser can guide you to the relevant product or journey.','/en/contact/']],
      'ar'=>[['نظرة عامة','افهم هدف هذه الصفحة وأهم استخداماتها.',''],['النقاط الأساسية','اطلع على المعلومات والشروط التي يجب دراستها قبل البدء.',''],['تحضير الطلب','اجمع المعلومات اللازمة لدراسة وضعيتك.',''],['المرافقة','يمكن لمستشار BDR توجيهك نحو المنتج أو المسار المناسب.','/ar/contact/']],
    ),
  );
  $features=$sets[$group][$lang] ?? $sets['general'][$lang];
  $aud=array(
    'fr'=>array('account'=>'Particuliers selon le type de compte et les conditions applicables.','card'=>'Clients répondant aux critères d’éligibilité de la carte et du compte associé.','credit'=>'Demandeurs répondant aux critères d’éligibilité du produit et dont le projet peut être financé.','saving'=>'Clients souhaitant constituer une épargne ou préparer un projet selon les conditions du support.','business'=>'Entreprises et professionnels selon leur activité, leur situation et les conditions d’accès aux services.','trade'=>'Entreprises réalisant des opérations d’importation ou d’exportation et répondant aux règles applicables.','agri'=>'Exploitants, entreprises et porteurs de projets selon les conditions du produit.','islamic'=>'Clients et professionnels selon les conditions propres à chaque solution de finance islamique.','digital'=>'Clients disposant d’un accès activé au service concerné.','institution'=>'Acteurs et organismes correspondant au périmètre de la rubrique.','service'=>'Toute personne ayant besoin d’une information ou d’un parcours BDR.','general'=>'Toute personne correspondant au public et aux conditions du service concerné.'),
    'en'=>array('account'=>'Individuals according to the account type and applicable conditions.','card'=>'Customers meeting the eligibility criteria of the card and associated account.','credit'=>'Applicants meeting product eligibility criteria whose project can be financed.','saving'=>'Customers wishing to save or prepare a project under the relevant product conditions.','business'=>'Businesses and professionals according to their activity, profile and access conditions.','trade'=>'Businesses carrying out import or export operations under applicable rules.','agri'=>'Farmers, businesses and project owners subject to product conditions.','islamic'=>'Customers and professionals according to the conditions of each Islamic finance solution.','digital'=>'Customers with an activated access to the relevant service.','institution'=>'Organisations and stakeholders within the scope of this section.','service'=>'Anyone needing BDR information or a service journey.','general'=>'Anyone matching the audience and conditions of the relevant service.'),
    'ar'=>array('account'=>'الأفراد حسب نوع الحساب والشروط المعمول بها.','card'=>'العملاء المستوفون لشروط أهلية البطاقة والحساب المرتبط بها.','credit'=>'المتقدمون الذين يستوفون شروط أهلية المنتج ويمكن تمويل مشاريعهم.','saving'=>'العملاء الراغبون في الادخار أو تحضير مشروع وفق شروط المنتج.','business'=>'المؤسسات والمهنيون حسب نشاطهم ووضعيتهم وشروط الاستفادة.','trade'=>'المؤسسات التي تقوم بعمليات الاستيراد أو التصدير وفق القواعد المعمول بها.','agri'=>'الفلاحون والمؤسسات وحاملو المشاريع حسب شروط المنتج.','islamic'=>'العملاء والمهنيون حسب شروط كل حل من حلول المالية الإسلامية.','digital'=>'العملاء الذين لديهم وصول مفعل للخدمة المعنية.','institution'=>'الهيئات والجهات التي تدخل ضمن نطاق هذه الصفحة.','service'=>'كل من يحتاج إلى معلومات أو مسار خدمة لدى BDR.','general'=>'كل شخص يستوفي الجمهور والشروط الخاصة بالخدمة.'),
  );
  $docs=array(
    'fr'=>'Pièce d’identité, justificatifs de situation, documents financiers ou pièces relatives au projet peuvent être demandés selon la démarche.',
    'en'=>'Identity documents, proof of status, financial information or project documents may be requested depending on the journey.',
    'ar'=>'قد تُطلب وثائق الهوية وإثبات الوضعية والبيانات المالية أو وثائق المشروع حسب الإجراء.'
  );
  $simulate=in_array($slug,array('credit-immobilier','credit-consommation','credit-auto','credits'),true);
  return array('title'=>$title,'eyebrow'=>$ey,'intro'=>$intro,'features'=>$features,'audience'=>$aud[$lang][$group]??$aud[$lang]['general'],'docs'=>$docs[$lang],'simulate'=>$simulate,'group'=>$group);
}

function bdr_v11_ui($lang){
  $u=array(
    'fr'=>array('home'=>'Accueil','previous'=>'Page précédente','back'=>'Retour','all'=>'Voir toutes les solutions','contact'=>'Nous contacter','branches'=>'Trouver une agence','open'=>'Ouvrir un compte','learn'=>'En savoir plus →','more'=>'Aller plus loin','faq'=>'Questions fréquentes','aud'=>'À qui s’adresse cette solution ?','key'=>'L’essentiel en un coup d’œil','characteristics'=>'Caractéristiques et points d’attention','docs'=>'Pièces à préparer','path'=>'Votre parcours','next'=>'Les prochaines étapes','noncontract'=>'Les informations sont générales et doivent être confirmées dans les conditions et documents en vigueur.','from'=>'BDR','official'=>'Source officielle','submit'=>'Envoyer ma demande'),
    'en'=>array('home'=>'Home','previous'=>'Previous page','back'=>'Back','all'=>'View all solutions','contact'=>'Contact us','branches'=>'Find a branch','open'=>'Open an account','learn'=>'Learn more →','more'=>'Go further','faq'=>'Frequently asked questions','aud'=>'Who is this solution for?','key'=>'At a glance','characteristics'=>'Key features and points to consider','docs'=>'Documents to prepare','path'=>'Your journey','next'=>'Next steps','noncontract'=>'Information is general and must be confirmed against current terms and documents.','from'=>'BDR','official'=>'Official source','submit'=>'Send my request'),
    'ar'=>array('home'=>'الرئيسية','previous'=>'الصفحة السابقة','back'=>'رجوع','all'=>'عرض جميع الحلول','contact'=>'اتصل بنا','branches'=>'العثور على وكالة','open'=>'فتح حساب','learn'=>'اعرف المزيد ←','more'=>'تابع الاستكشاف','faq'=>'الأسئلة الشائعة','aud'=>'لمن هذا الحل؟','key'=>'أهم المعلومات','characteristics'=>'الخصائص والنقاط الواجب الانتباه إليها','docs'=>'الوثائق الواجب تحضيرها','path'=>'مسارك','next'=>'الخطوات التالية','noncontract'=>'المعلومات عامة ويجب تأكيدها بالرجوع إلى الشروط والوثائق السارية.','from'=>'BDR','official'=>'المصدر الرسمي','submit'=>'إرسال الطلب'),
  );
  return $u[$lang] ?? $u['fr'];
}

function bdr_v11_localized_breadcrumbs($slug,$lang){
  if ($slug==='') return;
  $u=bdr_v11_ui($lang); $chain=array(); $cur=$slug; $guard=0;
  while($cur && $guard++<8){array_unshift($chain,$cur);$cur=bdr_v11_parent_slug($cur);}
  echo '<nav class="bdr-breadcrumbs" aria-label="'.esc_attr($lang==='ar'?'مسار التنقل':($lang==='en'?'Breadcrumb':'Fil d’Ariane')).'"><div class="container"><a href="'.esc_url(bdr_v11_url('', $lang)).'">'.esc_html($u['home']).'</a><span>›</span>';
  foreach($chain as $i=>$item){$label=bdr_v11_title($item,$lang); if($i===count($chain)-1) echo '<strong>'.esc_html($label).'</strong>'; else echo '<a href="'.esc_url(bdr_v11_url($item,$lang)).'">'.esc_html($label).'</a><span>›</span>';}
  echo '</div></nav>';
}
function bdr_v11_title($slug,$lang){
  $c=bdr_v11_catalog(); return isset($c[$slug][$lang])?$c[$slug][$lang]:($c[$slug]['fr']??ucwords(str_replace('-',' ',$slug)));
}

function bdr_v11_localized_contact_form($lang,$context='Request'){
  $t=array(
    'fr'=>array('name'=>'Nom et prénom *','email'=>'Adresse e-mail *','phone'=>'Téléphone','subject'=>'Objet de la demande','message'=>'Votre demande *','placeholder'=>'Décrivez votre demande…','consent'=>'J’accepte que la BDR utilise ces informations uniquement pour répondre à ma demande.','submit'=>'Envoyer ma demande','note'=>'Les champs marqués d’un * sont obligatoires. N’envoyez jamais de mot de passe, code de sécurité ou identifiant bancaire.'),
    'en'=>array('name'=>'Full name *','email'=>'Email address *','phone'=>'Phone','subject'=>'Request subject','message'=>'Your request *','placeholder'=>'Describe your request…','consent'=>'I agree that BDR may use this information only to respond to my request.','submit'=>'Send my request','note'=>'Fields marked * are required. Never send passwords, security codes or banking credentials through this form.'),
    'ar'=>array('name'=>'الاسم واللقب *','email'=>'البريد الإلكتروني *','phone'=>'الهاتف','subject'=>'موضوع الطلب','message'=>'طلبك *','placeholder'=>'صف طلبك…','consent'=>'أوافق على استخدام BDR لهذه المعلومات فقط للرد على طلبي.','submit'=>'إرسال الطلب','note'=>'الحقول التي تحمل * إلزامية. لا ترسل أبداً كلمات المرور أو رموز الأمان أو بيانات الدخول البنكية عبر هذا النموذج.'),
  );
  $x=$t[$lang]??$t['fr']; $uid=wp_unique_id('bdr-lform-'); $return_url=isset($_SERVER['REQUEST_URI'])?home_url(wp_unslash($_SERVER['REQUEST_URI'])):home_url('/');
  ob_start();
  echo '<form class="bdr-contact-form" method="post" action="'.esc_url(home_url('/')).'">';
  wp_nonce_field('bdr_contact_submit','bdr_contact_nonce');
  echo '<input type="hidden" name="bdr_contact_action" value="send"><input type="hidden" name="bdr_contact_context" value="'.esc_attr($context).'"><input type="hidden" name="bdr_return_url" value="'.esc_attr($return_url).'">';
  echo '<div class="contact-grid">';
  foreach(array(array('key'=>'name','name'=>'bdr_name','type'=>'text','req'=>true),array('key'=>'email','name'=>'bdr_email','type'=>'email','req'=>true),array('key'=>'phone','name'=>'bdr_phone','type'=>'tel','req'=>false)) as $f){
    echo '<div class="form-row"><label for="'.esc_attr($f['name'].'-'.$uid).'">'.esc_html($x[$f['key']]).'</label><input id="'.esc_attr($f['name'].'-'.$uid).'" name="'.esc_attr($f['name']).'" type="'.esc_attr($f['type']).'" '.($f['req']?'required ':'').'maxlength="190"></div>';
  }
  echo '<div class="form-row"><label for="bdr-subject-'.$uid.'">'.esc_html($x['subject']).'</label><select id="bdr-subject-'.$uid.'" name="bdr_subject"><option>'.esc_html($context).'</option><option>'.esc_html($lang==='ar'?'فتح حساب':($lang==='en'?'Account opening':'Ouverture de compte')).'</option><option>'.esc_html($lang==='ar'?'بطاقة بنكية':($lang==='en'?'Bank card':'Carte bancaire')).'</option><option>'.esc_html($lang==='ar'?'تمويل':($lang==='en'?'Financing':'Crédit / financement')).'</option><option>'.esc_html($lang==='ar'?'الادخار':($lang==='en'?'Savings':'Épargne')).'</option><option>'.esc_html($lang==='ar'?'المؤسسات':($lang==='en'?'Business solutions':'Solutions entreprises')).'</option><option>'.esc_html($lang==='ar'?'الخدمات الرقمية':($lang==='en'?'Online banking':'Banque en ligne')).'</option><option>'.esc_html($lang==='ar'?'وكالة':($lang==='en'?'Branch':'Agence')).'</option></select></div>';
  echo '</div><div class="form-row"><label for="bdr-message-'.$uid.'">'.esc_html($x['message']).'</label><textarea id="bdr-message-'.$uid.'" name="bdr_message" rows="5" required maxlength="3000" placeholder="'.esc_attr($x['placeholder']).'"></textarea></div>';
  echo '<div class="contact-consent"><label><input type="checkbox" name="bdr_consent" value="1" required> '.esc_html($x['consent']).'</label></div><div class="bdr-hp" aria-hidden="true"><label>Do not fill<input type="text" name="bdr_website" tabindex="-1" autocomplete="off"></label></div>';
  echo '<button class="btn btn-primary" type="submit">'.esc_html($x['submit']).'</button><p class="form-note">'.esc_html($x['note']).'</p></form>';
  return ob_get_clean();
}



function bdr_v11_related($items){ bdr_related($items); }


/* ========================= V12 – localized legal content ========================= */
function bdr_v12_legal_sections($lang){
  $data=array(
    'fr'=>array(
      'intro'=>'Cette page présente les règles générales applicables à l’utilisation du site BDR. Les informations d’identification de l’établissement, de l’hébergeur et du responsable de publication doivent être complétées avec les données juridiques officielles avant la mise en production définitive.',
      'sections'=>array(
        array('Éditeur du site','Le site est édité pour le compte de la Banque de Développement Régional (BDR). La dénomination sociale exacte, la forme juridique, le capital social, l’adresse du siège, le registre de commerce, les références d’agrément et l’identité du responsable de publication doivent être renseignés conformément aux informations officielles de l’établissement.'),
        array('Siège et informations institutionnelles','Les coordonnées officielles du siège social, les moyens de contact institutionnels et, le cas échéant, les informations relatives à l’autorité de supervision doivent être publiés de manière exacte et à jour. Ces informations prévalent sur toute présentation synthétique figurant sur une autre page du site.'),
        array('Hébergement','Les nom, adresse et coordonnées de l’hébergeur doivent être indiqués conformément au contrat d’hébergement en vigueur. Toute modification de l’hébergement doit faire l’objet d’une mise à jour de la présente page.'),
        array('Accès et utilisation du site','Le site est destiné à fournir des informations générales sur la banque, ses produits, ses services et ses canaux de contact. L’utilisateur s’engage à utiliser le site conformément aux lois et règlements applicables et à ne pas perturber son fonctionnement.'),
        array('Informations bancaires et conditions contractuelles','Les pages du site ont une vocation informative. Les tarifs, conditions générales, conventions, notices et documents contractuels applicables au produit concerné prévalent sur toute présentation résumée. Une demande en ligne ne vaut pas, à elle seule, acceptation d’un produit ou conclusion d’un contrat.'),
        array('Propriété intellectuelle','Les textes, marques, logos, photographies, illustrations, interfaces, bases de données et autres éléments présents sur le site sont protégés par les droits applicables. Toute reproduction, représentation, adaptation ou réutilisation non autorisée est interdite, sauf dans les limites prévues par la réglementation.'),
        array('Responsabilité','La banque s’efforce de maintenir des informations exactes et à jour. Elle ne peut toutefois garantir l’exhaustivité ou l’absence d’erreur de toute information publiée à un instant donné. L’utilisateur doit vérifier les conditions en vigueur avant toute décision ou opération.'),
        array('Disponibilité et maintenance','Le site peut être temporairement inaccessible en raison d’opérations de maintenance, d’évolutions techniques, d’incidents ou de contraintes liées aux réseaux de communication. Les interruptions temporaires ne donnent pas droit à indemnisation en elles-mêmes.'),
        array('Sécurité et fraude','La banque ne demande jamais par un formulaire général du site un mot de passe, un code PIN, un code OTP ou des identifiants bancaires. L’utilisateur doit vérifier l’adresse du site, éviter les liens suspects et signaler rapidement toute tentative de fraude par les canaux officiels.'),
        array('Données personnelles','Les informations personnelles transmises via les formulaires et services du site sont traitées selon les finalités et modalités décrites dans la politique de protection des données applicable. Pour les détails sur les droits, les durées de conservation et les modalités de contact, consultez la page dédiée aux données personnelles.'),
        array('Cookies et technologies similaires','Le site peut utiliser des cookies ou technologies similaires nécessaires à son fonctionnement, à la sécurité, à la mesure d’audience ou à certaines fonctionnalités. Les modalités applicables doivent être précisées dans la politique de cookies et les outils de gestion proposés sur le site.'),
        array('Liens externes','Le site peut contenir des liens vers des sites ou services tiers. Ces liens sont fournis pour faciliter l’accès à des informations complémentaires. La banque ne contrôle pas nécessairement le contenu ou les pratiques de ces sites tiers et invite l’utilisateur à consulter leurs propres conditions.'),
        array('Services bancaires en ligne','Les espaces de banque en ligne et les applications officielles disposent de leurs propres mécanismes d’authentification et de sécurité. Les éléments de démonstration éventuellement présents sur le site public ne constituent pas un service bancaire opérationnel et ne doivent jamais recevoir de véritables identifiants.'),
        array('Modification des mentions légales','Les présentes mentions peuvent être modifiées afin de tenir compte des évolutions réglementaires, techniques ou institutionnelles. La version publiée sur cette page est celle applicable à compter de sa date de mise à jour.'),
        array('Droit applicable et juridiction','Les présentes mentions sont soumises au droit applicable en République algérienne démocratique et populaire. Les règles de compétence juridictionnelle applicables aux litiges sont déterminées conformément à la réglementation en vigueur.'),
        array('Contact','Pour toute question relative au site, utilisez les coordonnées officielles publiées par la banque. Pour les demandes générales du site : contact@bdr-dz.com. Ne transmettez jamais de données d’authentification bancaire par e-mail ou via un formulaire général.')
      )
    ),
    'en'=>array(
      'intro'=>'This page sets out the general rules governing the use of the BDR website. The bank’s legal identity, hosting provider and publication details must be completed with the institution’s official information before final production launch.',
      'sections'=>array(
        array('Website publisher','The website is published on behalf of the Regional Development Bank (BDR). The exact legal name, legal form, share capital, registered office, commercial registration, regulatory authorisation references and publication manager must be completed using the bank’s official information.'),
        array('Registered office and institutional information','Official registered-office details, institutional contact channels and, where applicable, supervisory information should be published accurately and kept up to date. These official details prevail over any summary presentation elsewhere on the website.'),
        array('Hosting','The hosting provider’s name, address and contact details must be stated in accordance with the current hosting agreement. Any change of hosting provider should lead to an update of this page.'),
        array('Access and use of the website','The website provides general information about the bank, its products, services and contact channels. Users must use the website in accordance with applicable laws and must not disrupt its operation.'),
        array('Banking information and contractual terms','Website pages are provided for information purposes. Applicable tariffs, general terms, agreements, notices and contractual documents prevail over any summary presentation. An online request does not, by itself, constitute acceptance of a product or conclusion of a contract.'),
        array('Intellectual property','Texts, trademarks, logos, photographs, illustrations, interfaces, databases and other website elements are protected by applicable rights. Unauthorised reproduction, representation, adaptation or reuse is prohibited except where permitted by law.'),
        array('Liability','The bank seeks to keep published information accurate and current. It cannot, however, guarantee that every item is exhaustive or free from error at all times. Users should verify current terms before making a decision or carrying out a transaction.'),
        array('Availability and maintenance','The website may be temporarily unavailable because of maintenance, technical changes, incidents or communication-network constraints. Temporary interruptions do not in themselves create a right to compensation.'),
        array('Security and fraud prevention','The bank will not request passwords, PINs, one-time passwords or banking credentials through a general website contact form. Users should verify the website address, avoid suspicious links and promptly report suspected fraud through official channels.'),
        array('Personal data','Personal information submitted through forms and website services is processed for the purposes and under the conditions described in the applicable personal-data policy. For rights, retention periods and dedicated contact details, please consult the relevant privacy page.'),
        array('Cookies and similar technologies','The website may use cookies or similar technologies required for operation, security, audience measurement or certain features. Applicable rules should be detailed in the cookie policy and the consent-management tools provided on the website.'),
        array('External links','The website may link to third-party websites or services for additional information. The bank does not necessarily control their content or practices and encourages users to review the terms and privacy policies of those third parties.'),
        array('Online banking services','Online banking areas and official applications have their own authentication and security mechanisms. Any demonstration interface on the public website is not an operational banking service and must never receive real credentials.'),
        array('Changes to this legal notice','These legal notices may be updated to reflect regulatory, technical or institutional changes. The version published on this page is the version applicable from its stated update date.'),
        array('Applicable law and jurisdiction','These legal notices are governed by the laws applicable in the People’s Democratic Republic of Algeria. Jurisdiction over disputes is determined in accordance with applicable law.'),
        array('Contact','For questions about the website, use the official contact details published by the bank. For general website requests: contact@bdr-dz.com. Never send banking credentials by email or through a general contact form.')
      )
    ),
    'ar'=>array(
      'intro'=>'توضح هذه الصفحة القواعد العامة المطبقة على استخدام موقع BDR. يجب استكمال هوية البنك القانونية وبيانات الاستضافة ومسؤول النشر بالمعلومات الرسمية للمؤسسة قبل الإطلاق النهائي للموقع.',
      'sections'=>array(
        array('ناشر الموقع','يُنشر الموقع لحساب بنك التنمية الجهوية BDR. يجب إدراج الاسم القانوني الكامل والشكل القانوني ورأس المال والمقر الاجتماعي والسجل التجاري ومراجع الاعتماد والجهة أو الشخص المسؤول عن النشر وفق المعلومات الرسمية للبنك.'),
        array('المقر والمعلومات المؤسساتية','يجب نشر بيانات المقر الرسمي ووسائل الاتصال المؤسساتية، وعند الاقتضاء معلومات الجهة الرقابية، بصورة دقيقة ومحدثة. وتبقى المعلومات الرسمية هي المرجع المعتمد.'),
        array('الاستضافة','يجب إدراج اسم وعنوان وبيانات الاتصال الخاصة بمزود الاستضافة وفق عقد الاستضافة الساري. ويجب تحديث هذه الصفحة عند تغيير مزود الاستضافة.'),
        array('الدخول إلى الموقع واستخدامه','يقدم الموقع معلومات عامة حول البنك ومنتجاته وخدماته وقنوات الاتصال به. يلتزم المستخدم باستعمال الموقع وفق القوانين والتنظيمات المعمول بها وعدم تعطيل عمله.'),
        array('المعلومات البنكية والشروط التعاقدية','المحتوى المنشور على الموقع ذو طبيعة إعلامية. وتبقى التعريفات والشروط العامة والاتفاقيات والإشعارات والوثائق التعاقدية السارية هي المرجع. ولا يُعد الطلب الإلكتروني وحده قبولاً بمنتج أو إبراماً لعقد.'),
        array('الملكية الفكرية','تخضع النصوص والعلامات والشعارات والصور والرسومات والواجهات وقواعد البيانات وغيرها من عناصر الموقع للحماية القانونية المعمول بها. ويُمنع نسخها أو إعادة استخدامها دون ترخيص، إلا في الحدود التي يسمح بها القانون.'),
        array('المسؤولية','يسعى البنك إلى المحافظة على دقة المعلومات المنشورة وتحديثها. ومع ذلك لا يمكن ضمان اكتمال جميع المعلومات أو خلوها من الأخطاء في كل وقت. ويجب على المستخدم التحقق من الشروط السارية قبل اتخاذ أي قرار أو إجراء.'),
        array('التوفر والصيانة','قد يتعذر الوصول إلى الموقع مؤقتاً بسبب أعمال الصيانة أو التطوير أو الحوادث التقنية أو قيود شبكات الاتصال. ولا تنشأ عن التوقفات المؤقتة وحدها أي مطالبة بالتعويض.'),
        array('الأمان ومكافحة الاحتيال','لا يطلب البنك كلمات المرور أو أرقام PIN أو رموز الاستخدام لمرة واحدة أو بيانات الدخول البنكية عبر نموذج اتصال عام. يجب التحقق من عنوان الموقع وتجنب الروابط المشبوهة والإبلاغ سريعاً عن أي محاولة احتيال عبر القنوات الرسمية.'),
        array('البيانات الشخصية','تُعالج المعلومات الشخصية المرسلة عبر النماذج والخدمات وفق الأغراض والقواعد المحددة في سياسة حماية البيانات المعمول بها. ولمعرفة الحقوق ومدد الاحتفاظ ووسائل الاتصال المخصصة، يرجى الرجوع إلى الصفحة الخاصة بالبيانات الشخصية.'),
        array('ملفات تعريف الارتباط والتقنيات المماثلة','قد يستخدم الموقع ملفات تعريف الارتباط أو تقنيات مماثلة اللازمة للتشغيل والأمان وقياس الجمهور وبعض الوظائف. ويجب توضيح القواعد المطبقة في سياسة ملفات تعريف الارتباط وأدوات إدارة الموافقة.'),
        array('الروابط الخارجية','قد يتضمن الموقع روابط إلى مواقع أو خدمات تابعة لجهات أخرى لتوفير معلومات إضافية. ولا يتحكم البنك بالضرورة في محتوى هذه المواقع أو ممارساتها، ويوصى بالاطلاع على شروطها وسياسات الخصوصية الخاصة بها.'),
        array('الخدمات البنكية عبر الإنترنت','تخضع مساحات الخدمات البنكية عبر الإنترنت والتطبيقات الرسمية لآليات المصادقة والأمان الخاصة بها. وأي واجهة تجريبية موجودة في الموقع العام ليست خدمة بنكية تشغيلية ولا يجوز إدخال بيانات حقيقية فيها.'),
        array('تعديل الإشعارات القانونية','يمكن تعديل هذه الإشعارات لمواكبة التطورات التنظيمية أو التقنية أو المؤسساتية. وتكون النسخة المنشورة على هذه الصفحة هي النسخة المطبقة ابتداءً من تاريخ تحديثها.'),
        array('القانون الواجب التطبيق والاختصاص','تخضع هذه الإشعارات للقوانين المعمول بها في الجمهورية الجزائرية الديمقراطية الشعبية. ويحدد الاختصاص القضائي للنزاعات وفق القواعد القانونية السارية.'),
        array('الاتصال','لأي استفسار يتعلق بالموقع، يرجى استعمال بيانات الاتصال الرسمية المنشورة من طرف البنك. للطلبات العامة عبر الموقع: contact@bdr-dz.com. لا ترسل أبداً بيانات الدخول البنكية عبر البريد الإلكتروني أو نموذج اتصال عام.')
      )
    )
  );
  return $data[$lang] ?? $data['fr'];
}




/* V11.4 page-localisation helpers. */

function bdr_v11_is_lang_route(){ return (bool)get_query_var('bdr_lang') || (bool)get_query_var('bdr_front'); }

function bdr_v11_special_copy($slug,$lang){
  $all=array(
    'ouvrir-un-compte'=>array(
      'fr'=>array('ey'=>'DEVENIR CLIENT','title'=>'Ouvrir un compte','intro'=>'Préparez votre démarche en quelques étapes. Cette page présente le parcours avant toute prise de contact.','choose'=>'Quel compte choisir ?','journey'=>'Le parcours d’ouverture','start'=>'Commencer la démarche','help'=>'Besoin d’un accompagnement ?','request'=>'Demander des informations'),
      'en'=>array('ey'=>'BECOME A CUSTOMER','title'=>'Open an account','intro'=>'Prepare your account-opening journey in a few clear steps before contacting BDR.','choose'=>'Which account should you choose?','journey'=>'The account-opening journey','start'=>'Start your journey','help'=>'Need support?','request'=>'Request information'),
      'ar'=>array('ey'=>'أن تصبح عميلاً','title'=>'فتح حساب','intro'=>'حضّر مسار فتح الحساب في خطوات واضحة قبل التواصل مع BDR.','choose'=>'أي حساب تختار؟','journey'=>'مسار فتح الحساب','start'=>'بدء الإجراء','help'=>'هل تحتاج إلى مرافقة؟','request'=>'طلب المعلومات')
    ),
    'contact'=>array(
      'fr'=>array('ey'=>'RELATION CLIENT','title'=>'Contactez la BDR','intro'=>'Trouvez le bon canal pour une question sur un produit, une agence ou une démarche.','before'=>'Avant de nous écrire','request'=>'Votre demande','email'=>'Par e-mail','branch'=>'En agence'),
      'en'=>array('ey'=>'CUSTOMER RELATIONSHIP','title'=>'Contact BDR','intro'=>'Choose the right channel for a question about a product, branch or request.','before'=>'Before you contact us','request'=>'Your request','email'=>'By email','branch'=>'At a branch'),
      'ar'=>array('ey'=>'علاقة العملاء','title'=>'اتصل بـ BDR','intro'=>'اختر القناة المناسبة للاستفسار عن منتج أو وكالة أو إجراء.','before'=>'قبل إرسال طلبك','request'=>'طلبك','email'=>'البريد الإلكتروني','branch'=>'في الوكالة')
    ),
    'taux-de-change'=>array(
      'fr'=>array('ey'=>'INFORMATIONS MARCHÉ','title'=>'Taux de change','intro'=>'Dernière cotation officielle disponible du dinar algérien. Les valeurs ci-dessous sont affichées à titre informatif et ne constituent pas un tarif commercial BDR.','source'=>'Source officielle','source_desc'=>'Consultez la source officielle de la Banque d’Algérie pour les taux de change journaliers.','view'=>'Voir les taux de change journaliers'),
      'en'=>array('ey'=>'MARKET INFORMATION','title'=>'Exchange rates','intro'=>'Latest available official quotation for the Algerian dinar. The values below are provided for information only and are not BDR commercial rates.','source'=>'Official source','source_desc'=>'Consult the official Bank of Algeria source for daily exchange rates.','view'=>'View daily exchange rates'),
      'ar'=>array('ey'=>'معلومات السوق','title'=>'أسعار الصرف','intro'=>'آخر تسعيرة رسمية متاحة للدينار الجزائري. القيم أدناه للمعلومة فقط ولا تمثل تعريفة تجارية لدى BDR.','source'=>'المصدر الرسمي','source_desc'=>'اطلع على المصدر الرسمي لبنك الجزائر لأسعار الصرف اليومية.','view'=>'عرض أسعار الصرف اليومية')
    )
  );
  return $all[$slug][$lang] ?? ($all[$slug]['fr'] ?? array());
}

/* V15 : la navigation basse est rendue une seule fois par page.php (plus de doublons). */
function bdr_v11_localized_bottom($slug, $lang) { /* volontairement vide */ }
