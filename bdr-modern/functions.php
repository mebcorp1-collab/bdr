<?php
if (!defined('ABSPATH')) exit;

function bdr_v3_setup(){
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('custom-logo');
  register_nav_menus(array('primary'=>'Menu principal'));
}
add_action('after_setup_theme','bdr_v3_setup');




/* ---------- Public contact forms ---------- */
function bdr_v3_contact_form($context='Demande d’information'){
  $context = sanitize_text_field($context);
  $return_url = isset($_SERVER['REQUEST_URI']) ? home_url(wp_unslash($_SERVER['REQUEST_URI'])) : home_url('/');
  $uid = wp_unique_id('bdr-contact-');
  $sent = isset($_GET['bdr_contact']) && $_GET['bdr_contact']==='success';
  $error = isset($_GET['bdr_contact']) && $_GET['bdr_contact']==='error';
  ob_start();
  if($sent) echo '<div class="success">Votre demande a bien été envoyée. Notre équipe vous répondra à l’adresse e-mail indiquée.</div>';
  if($error) echo '<div class="form-error">Votre demande n’a pas pu être envoyée. Vérifiez les informations saisies puis réessayez.</div>';
  ?>
  <form class="bdr-contact-form" method="post" action="<?php echo esc_url(home_url('/')); ?>">
    <?php wp_nonce_field('bdr_contact_submit','bdr_contact_nonce'); ?>
    <input type="hidden" name="bdr_contact_action" value="send">
    <input type="hidden" name="bdr_contact_context" value="<?php echo esc_attr($context); ?>"><input type="hidden" name="bdr_return_url" value="<?php echo esc_attr($return_url); ?>">
    <div class="contact-grid">
      <div class="form-row"><label for="bdr-name-<?php echo esc_attr($uid); ?>">Nom et prénom *</label><input id="bdr-name-<?php echo esc_attr($uid); ?>" name="bdr_name" type="text" required maxlength="120"></div>
      <div class="form-row"><label for="bdr-email-<?php echo esc_attr($uid); ?>">Adresse e-mail *</label><input id="bdr-email-<?php echo esc_attr($uid); ?>" name="bdr_email" type="email" required maxlength="190"></div>
      <div class="form-row"><label for="bdr-phone-<?php echo esc_attr($uid); ?>">Téléphone</label><input id="bdr-phone-<?php echo esc_attr($uid); ?>" name="bdr_phone" type="tel" maxlength="40"></div>
      <div class="form-row"><label for="bdr-subject-<?php echo esc_attr($uid); ?>">Objet de la demande</label><select id="bdr-subject-<?php echo esc_attr($uid); ?>" name="bdr_subject">
        <option><?php echo esc_html($context); ?></option>
        <option>Ouverture de compte</option><option>Carte bancaire</option><option>Crédit / financement</option><option>Épargne</option><option>Solutions entreprises</option><option>Banque en ligne</option><option>Agence</option><option>Autre demande</option>
      </select></div>
    </div>
    <div class="form-row"><label for="bdr-message-<?php echo esc_attr($uid); ?>">Votre demande *</label><textarea id="bdr-message-<?php echo esc_attr($uid); ?>" name="bdr_message" rows="5" required maxlength="3000" placeholder="Décrivez votre demande…"></textarea></div>
    <div class="contact-consent"><label><input type="checkbox" name="bdr_consent" value="1" required> J’accepte que la BDR utilise ces informations uniquement pour répondre à ma demande.</label></div>
    <div class="bdr-hp" aria-hidden="true"><label>Ne pas remplir<input type="text" name="bdr_website" tabindex="-1" autocomplete="off"></label></div>
    <button class="btn btn-primary" type="submit">Envoyer ma demande</button>
    <p class="form-note">Les champs marqués d’un * sont obligatoires. N’envoyez jamais de mot de passe, code de sécurité ou identifiant bancaire par ce formulaire.</p>
  </form>
  <?php
  return ob_get_clean();
}

function bdr_v3_handle_contact_form(){
  if($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['bdr_contact_action'])) return;
  if(!isset($_POST['bdr_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bdr_contact_nonce'])),'bdr_contact_submit')) return;
  if(!empty($_POST['bdr_website'])) return;

  $name = sanitize_text_field(wp_unslash($_POST['bdr_name'] ?? ''));
  $email = sanitize_email(wp_unslash($_POST['bdr_email'] ?? ''));
  $phone = sanitize_text_field(wp_unslash($_POST['bdr_phone'] ?? ''));
  $subject = sanitize_text_field(wp_unslash($_POST['bdr_subject'] ?? 'Demande d’information'));
  $message = sanitize_textarea_field(wp_unslash($_POST['bdr_message'] ?? ''));
  $consent = !empty($_POST['bdr_consent']);
  $context = sanitize_text_field(wp_unslash($_POST['bdr_contact_context'] ?? 'Demande d’information'));
  $return_url = esc_url_raw(wp_unslash($_POST['bdr_return_url'] ?? home_url('/')));
  if(strpos($return_url, home_url('/')) !== 0) $return_url = home_url('/');

  if(!$name || !$email || !is_email($email) || !$message || !$consent){
    wp_safe_redirect(add_query_arg('bdr_contact','error',$return_url));
    exit;
  }

  $to = bdr_v15_opt('email');
  $mail_subject = '[BDR] '.$subject;
  $body = "Nouvelle demande depuis le site BDR\n\n";
  $body .= "Type : {$context}\n";
  $body .= "Nom : {$name}\n";
  $body .= "E-mail : {$email}\n";
  $body .= "Téléphone : {$phone}\n\n";
  $body .= "Message :\n{$message}\n";
  $headers = array('Content-Type: text/plain; charset=UTF-8','Reply-To: '.$name.' <'.$email.'>');

  $ok = wp_mail($to,$mail_subject,$body,$headers);
  wp_safe_redirect(add_query_arg('bdr_contact',$ok ? 'success' : 'error',$return_url));
  exit;
}
add_action('init','bdr_v3_handle_contact_form');

/* ---------- Custom Post Types ---------- */
function bdr_v3_cpt(){
  register_post_type('bdr_offre', array(
    'labels'=>array('name'=>'Offres bancaires','singular_name'=>'Offre bancaire','add_new_item'=>'Ajouter une offre'),
    'public'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-money-alt',
    'supports'=>array('title','editor','thumbnail','excerpt'),'has_archive'=>true,
    'rewrite'=>array('slug'=>'offres')
  ));
  register_post_type('bdr_agence', array(
    'labels'=>array('name'=>'Agences','singular_name'=>'Agence','add_new_item'=>'Ajouter une agence'),
    'public'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-location-alt',
    'supports'=>array('title','editor'),'has_archive'=>false,
    'rewrite'=>array('slug'=>'agence')
  ));
  register_post_type('bdr_actualite', array(
    'labels'=>array('name'=>'Actualités BDR','singular_name'=>'Actualité','add_new_item'=>'Ajouter une actualité'),
    'public'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-megaphone',
    'supports'=>array('title','editor','thumbnail','excerpt'),'has_archive'=>true,
    'rewrite'=>array('slug'=>'actualites')
  ));
}
add_action('init','bdr_v3_cpt');

/* ---------- Meta fields ---------- */
function bdr_v3_meta_boxes(){
  add_meta_box('bdr_agence_meta','Informations agence','bdr_agence_box','bdr_agence','normal','high');
  add_meta_box('bdr_offre_meta','Informations offre','bdr_offre_box','bdr_offre','normal','high');
}
add_action('add_meta_boxes','bdr_v3_meta_boxes');

function bdr_agence_box($post){
  wp_nonce_field('bdr_agence_save','bdr_agence_nonce');
  $wilaya=get_post_meta($post->ID,'_bdr_wilaya',true);
  $ville=get_post_meta($post->ID,'_bdr_ville',true);
  $adresse=get_post_meta($post->ID,'_bdr_adresse',true);
  $tel=get_post_meta($post->ID,'_bdr_tel',true); $code=get_post_meta($post->ID,'_bdr_code',true); $lat=get_post_meta($post->ID,'_bdr_lat',true); $lng=get_post_meta($post->ID,'_bdr_lng',true);
  echo '<p><label>Wilaya<br><input class="widefat" name="bdr_wilaya" value="'.esc_attr($wilaya).'"></label></p>';
  echo '<p><label>Ville<br><input class="widefat" name="bdr_ville" value="'.esc_attr($ville).'"></label></p>';
  echo '<p><label>Adresse<br><input class="widefat" name="bdr_adresse" value="'.esc_attr($adresse).'"></label></p>';
  $verified=get_post_meta($post->ID,'_bdr_verified',true);
  echo '<p><label>Téléphone<br><input class="widefat" name="bdr_tel" value="'.esc_attr($tel).'"></label></p>';
  echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><label>Code agence<br><input class="widefat" name="bdr_code" value="'.esc_attr($code).'"></label><label>Latitude<br><input class="widefat" name="bdr_lat" value="'.esc_attr($lat).'"></label><label>Longitude<br><input class="widefat" name="bdr_lng" value="'.esc_attr($lng).'"></label></div>';
  echo '<p><label><input type="checkbox" name="bdr_verified" value="1" '.checked($verified,'1',false).'> Adresse et coordonnées vérifiées avant publication</label></p>';
}
function bdr_offre_box($post){
  wp_nonce_field('bdr_offre_save','bdr_offre_nonce');
  $category=get_post_meta($post->ID,'_bdr_category',true);
  $cta=get_post_meta($post->ID,'_bdr_cta',true);
  echo '<p><label>Catégorie<br><select class="widefat" name="bdr_category">';
  foreach(array('Comptes & Cartes','Crédits','Épargne','Entreprises','Services') as $x)
    echo '<option '.selected($category,$x,false).'>'.esc_html($x).'</option>';
  echo '</select></label></p>';
  echo '<p><label>Libellé du bouton<br><input class="widefat" name="bdr_cta" value="'.esc_attr($cta ?: 'Découvrir').'"></label></p>';
}
function bdr_v3_save_meta($post_id){
  if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if(isset($_POST['bdr_agence_nonce']) && wp_verify_nonce($_POST['bdr_agence_nonce'],'bdr_agence_save')){
    if(current_user_can('edit_post',$post_id)){
      foreach(array('wilaya','ville','adresse','tel','code','lat','lng') as $k)
        if(isset($_POST['bdr_'.$k])) update_post_meta($post_id,'_bdr_'.$k,sanitize_text_field(wp_unslash($_POST['bdr_'.$k])));
      update_post_meta($post_id,'_bdr_verified',!empty($_POST['bdr_verified'])?'1':'0');
    }
  }
  if(isset($_POST['bdr_offre_nonce']) && wp_verify_nonce($_POST['bdr_offre_nonce'],'bdr_offre_save')){
    if(current_user_can('edit_post',$post_id)){
      foreach(array('category','cta') as $k)
        if(isset($_POST['bdr_'.$k])) update_post_meta($post_id,'_bdr_'.$k,sanitize_text_field(wp_unslash($_POST['bdr_'.$k])));
    }
  }
}
add_action('save_post','bdr_v3_save_meta');



/* Génère un numéro de téléphone algérien fictif au format national. */


/* ---------- Agences BDR : réseau V13 importé depuis Excel ---------- */
function bdr_v13_agency_data() {
  return array(
    array('BDR-0001','Adrar','Adrar','PLACE DES MARTYRES','07 81 01 82 52',27.9763,-0.4842),
    array('BDR-0002','Adrar','Reggane','PLACE DES MARTYRS','07 33 65 26 92',26.7199,0.1711),
    array('BDR-0003','Adrar','Aoulef','RUE LARBI BEN M’HIDI 01300','07 58 60 83 35',26.9711,1.0595),
    array('BDR-0004','Chlef','Chlef','7 BOULEVARD EMIR ABDELKADER','06 67 88 75 70',36.1691,1.2839),
    array('BDR-0005','Chlef','Ténès','ROUTE DE MOSTAGANEM – SORTIE OUEST','06 98 89 99 20',36.5083,1.2427),
    array('BDR-0006','Chlef','El Karimia','CENTRE-VILLE – EL KARIMIA','06 85 39 51 74',36.0774,1.5112),
    array('BDR-0007','Laghouat','Laghouat','AVENUE DE L’INDEPENDANCE 03000','05 63 19 49 90',33.794,2.8369),
    array('BDR-0008','Laghouat','Aflou','Centre-ville, Aflou','07 93 98 53 82',34.1121,2.0835),
    array('BDR-0009','Laghouat','Ksar El Hirane','CENTRE COMMERCIAL – KSAR EL HIRANE','05 36 15 31 69',33.7855,3.1194),
    array('BDR-0010','Oum El Bouaghi','Oum El Bouaghi','BD HOUARI BOUMEDIENE 04000','07 74 36 02 11',35.8726,7.083),
    array('BDR-0011','Oum El Bouaghi','Aïn Beïda','ROUTE DE SEDRATA 04200','05 31 84 57 43',35.7948,7.3339),
    array('BDR-0012','Oum El Bouaghi','Meskiana','RUE RADJAI AMAR 04250','05 81 18 85 09',35.6297,7.6524),
    array('BDR-0013','Batna','Batna','PLACE DE LA LIBERTE FACE SNTV 05000','06 58 74 48 82',35.5784,6.1083),
    array('BDR-0014','Batna','Barika','RUE SMAIN BOURADI 05400','05 09 43 45 87',35.3699,5.1424),
    array('BDR-0015','Batna','Arris','RUE DU 1er MAI 05200','07 07 65 88 74',35.3105,6.2008),
    array('BDR-0016','Béjaïa','Béjaïa','RUE DE LA LIBERTÉ BP 163 06000','07 17 18 51 25',36.7696,4.9385),
    array('BDR-0017','Béjaïa','Akbou','97 RUE LARBI TOUATI 06200','06 93 04 48 01',36.4583,4.464),
    array('BDR-0018','Béjaïa','Kherrata','RUE DU CHAHID ALLIK LEMERI 06600','07 65 53 31 42',36.377,5.088),
    array('BDR-0019','Biskra','Biskra','02 RUE IBN BADIS 07000','06 00 67 06 37',34.8481,5.7281),
    array('BDR-0020','Biskra','Tolga','CITE 16 LOGEMENTS 07000','06 66 64 48 24',34.722,5.378),
    array('BDR-0021','Biskra','Sidi Okba','CITE DES 60 LOGT 07000','07 72 32 96 41',34.745,5.898),
    array('BDR-0022','Béchar','Béchar','RUE CDT FERRADJ – CENTRE VILLE','06 83 46 53 05',31.6238,-2.2162),
    array('BDR-0023','Béchar','Abadla','HAY EL DJORF N°22 BP N°13 08200','05 05 48 26 14',31.018999,-2.7239),
    array('BDR-0024','Béchar','Kenadsa','Centre-ville, Kenadsa','07 85 17 12 21',31.563,-2.432),
    array('BDR-0025','Blida','Blida','07 PLACE DU 1er NOVEMBRE 09000','07 60 64 11 05',36.4736,2.8323),
    array('BDR-0026','Blida','Boufarik','PLACE DE LA LIBERTÉ 09400','07 32 73 99 78',36.574,2.912),
    array('BDR-0027','Blida','Mouzaïa','20 RUE HATTAB AMAR 09210','06 83 06 06 39',36.466,2.689),
    array('BDR-0028','Bouira','Bouira','RUE AMROUCHE MOULOUD 10000','07 28 46 13 03',36.3749,3.902),
    array('BDR-0029','Bouira','Sour El Ghozlane','12 PLACE DE L’INDÉPENDANCE 10300','05 25 48 25 45',36.147,3.686),
    array('BDR-0030','Bouira','Lakhdaria','CITE DES 56 LGTS BT N°02 10200','07 61 91 35 71',36.564,3.591),
    array('BDR-0031','Tamanrasset','Tamanrasset','KSAR EL FOUGANI 11000','06 48 81 52 36',22.785,5.5228),
    array('BDR-0032','Tamanrasset','In Amguel','Centre-ville, In Amguel','05 77 64 26 59',23.69,5.15),
    array('BDR-0033','Tamanrasset','Tazrouk','Centre-ville, Tazrouk','05 81 46 89 25',23.45,5.78),
    array('BDR-0034','Tébessa','Tébessa','Centre-ville, Tébessa','05 56 56 95 51',35.4042,8.124),
    array('BDR-0035','Tébessa','Chéria','Centre-ville, Chéria','05 31 76 53 64',35.2722,7.7511),
    array('BDR-0036','Tébessa','Hammamet','Centre-ville, Hammamet','06 09 61 43 17',35.448,7.952),
    array('BDR-0037','Tlemcen','Tlemcen','01 RUE DE LA PAIX 13000','05 10 66 48 98',34.8959,-1.3501),
    array('BDR-0038','Tlemcen','Maghnia','03 BD MED KHEMISTI 13300','06 85 43 04 93',34.8482,-1.7802),
    array('BDR-0039','Tlemcen','Ghazaouet','AVENUE SAYEH MISSOUM','05 42 42 16 54',35.0934,-1.8721),
    array('BDR-0040','Tiaret','Tiaret','1 RUE PASTEUR 14000','05 55 88 72 53',35.371,1.316),
    array('BDR-0041','Tiaret','Frenda','BD DES MARTYRS FRENDA 14400','06 61 54 18 91',35.065,1.049),
    array('BDR-0042','Tiaret','Sougueur','RUE EMIR ABDELKADER SOUGUEUR 14200','06 05 84 32 32',35.185,1.495),
    array('BDR-0043','Tizi Ouzou','Tizi Ouzou','BD MOH SAID OUZEFFOUN 15000','05 05 16 53 43',36.7118,4.0459),
    array('BDR-0044','Tizi Ouzou','Azazga','RUE CDT AHMED ZAIDAT 15300','05 05 08 31 72',36.744,4.372),
    array('BDR-0045','Tizi Ouzou','Draâ El Mizan','CITE DES FONCTIONNAIRES – ROUTE DE BOGHNI BP 168 15400','07 94 52 71 69',36.536,3.833),
    array('BDR-0046','Alger','Alger-Centre','CENTRE-VILLE – ALGER','05 55 06 35 53',36.7538,3.0588),
    array('BDR-0047','Alger','Bab Ezzouar','CENTRE-VILLE – BAB EZZOUAR','06 88 84 08 77',36.72,3.185),
    array('BDR-0048','Alger','Chéraga','08 RUE ABANE RAMDANE 42300','05 62 70 88 54',36.7596,2.8933),
    array('BDR-0049','Djelfa','Djelfa','CITE SAADAT 17000','07 57 84 59 51',34.6741,3.2504),
    array('BDR-0050','Djelfa','Hassi Bahbah','ROUTE D’ALGER 17200','05 20 67 65 62',35.0764,2.9959),
    array('BDR-0051','Djelfa','Messaad','CENTRE VILLE – PLACE APC 17400','05 52 52 30 20',34.154,3.494),
    array('BDR-0052','Jijel','Jijel','AVENUE EMIR ABDELKADER 18000','05 47 54 81 16',36.8206,5.7667),
    array('BDR-0053','Jijel','Taher','RUE AMAR TAHER 18200','05 46 73 38 67',36.77,5.899),
    array('BDR-0054','Jijel','El Milia','PLACE DES MARTYRS 18300','05 05 69 75 40',36.753,6.274),
    array('BDR-0055','Sétif','Sétif','AVENUE DU 8 MAI 1945','07 91 51 92 45',36.1911,5.4137),
    array('BDR-0056','Sétif','El Eulma','CENTRE-VILLE – EL EULMA','05 83 27 96 53',36.1528,5.69),
    array('BDR-0057','Sétif','Aïn Oulmene','CITE DES 100 LOGTS – BLOC D BP 162 19200','07 76 86 72 71',35.924,5.294),
    array('BDR-0058','Saïda','Saïda','17 AVENUE DES CHOUHADAS 20000','05 70 10 66 03',34.8303,0.1517),
    array('BDR-0059','Saïda','Aïn El Hadjar','AVENUE 1er NOVEMBRE 20100','06 34 88 29 94',34.758,0.145),
    array('BDR-0060','Saïda','Youb','Centre-ville, Youb','06 93 37 11 41',34.538,0.083),
    array('BDR-0061','Skikda','Skikda','23 RUE DIDOUCHE MOURAD 21000','06 46 50 10 55',36.8762,6.9092),
    array('BDR-0062','Skikda','Collo','RUE ZIGHOUD YOUCEF 21300','07 34 66 59 84',37.005,6.56),
    array('BDR-0063','Skikda','El Harrouch','RUE BACHIRE BOUKADOUM 21400','06 22 76 76 90',36.653,7.188),
    array('BDR-0064','Sidi Bel Abbès','Sidi Bel Abbès','CENTRE-VILLE – SIDI BEL ABBÈS','07 75 02 83 63',35.2063,-0.7002),
    array('BDR-0065','Sidi Bel Abbès','Telagh','45 BD MOHAMED KHEMISTI 22400','05 07 58 29 67',34.8037,-0.6044),
    array('BDR-0066','Sidi Bel Abbès','Sfisef','CENTRE-VILLE – SFISEF','05 22 61 56 55',35.23,-0.24),
    array('BDR-0067','Annaba','Annaba','20 COURS DE LA REVOLUTION 23000','05 47 17 77 95',36.9,7.7667),
    array('BDR-0068','Annaba','El Hadjar','RUE ZIGHOUD YOUCEF 23200','05 17 79 42 57',36.803,7.738),
    array('BDR-0069','Annaba','Berrahal','CENTRE COMMERCIAL BP 50 A 23100','07 99 86 60 40',36.835,7.454),
    array('BDR-0070','Guelma','Guelma','ANGLE DJUGURTHA – GUELMA','05 67 73 52 32',36.4621,7.4261),
    array('BDR-0071','Guelma','Bouchegouf','CENTRE-VILLE – BOUCHEGOUF','06 39 63 04 94',36.459,7.738),
    array('BDR-0072','Guelma','Oued Zenati','CENTRE-VILLE – OUED ZENATI','05 38 67 91 93',36.354,7.143),
    array('BDR-0073','Constantine','Constantine','CENTRE-VILLE – CONSTANTINE','07 32 41 12 49',36.365,6.6147),
    array('BDR-0074','Constantine','El Khroub','CENTRE-VILLE – EL KHROUB','05 61 10 15 11',36.263,6.587),
    array('BDR-0075','Constantine','Hamma Bouziane','CENTRE-VILLE – HAMMA BOUZIANE','05 04 48 27 20',36.412,6.596),
    array('BDR-0076','Médéa','Médéa','1 RUE DES FRERES BENTURKIA 26000','07 08 70 58 47',36.2642,2.7539),
    array('BDR-0077','Médéa','Berrouaghia','10 AVENUE DE LA REPUBLIQUE 26200','06 53 93 57 53',36.135,2.91),
    array('BDR-0078','Médéa','Ksar El Boukhari','60 BD MOHAMED KHEMISTI','05 26 71 72 97',35.888,2.732),
    array('BDR-0079','Mostaganem','Mostaganem','03 AVENUE BEN YAHIA BELKACEM 27000','05 55 29 72 90',35.9333,0.09),
    array('BDR-0080','Mostaganem','Aïn Tédelès','AVENUE DE LA GARE','06 57 75 45 40',35.945,0.302),
    array('BDR-0081','Mostaganem','Sidi Ali','CENTRE-VILLE – SIDI ALI','05 69 43 11 31',36.017,0.42),
    array('BDR-0082','M’Sila','M’Sila','NOUVELLE CITE ADMINISTRATIVE 28000','05 07 12 40 78',35.7211,4.3786),
    array('BDR-0083','M’Sila','Bou Saâda','RUE DE LA PALESTINE 28200','05 06 99 85 44',35.2131,4.0429),
    array('BDR-0084','M’Sila','Sidi Aïssa','ROUTE PRINCIPALE 28300','07 19 76 03 54',35.885,3.776),
    array('BDR-0085','Mascara','Mascara','CENTRE-VILLE – MASCARA','05 52 34 73 56',35.396,0.14),
    array('BDR-0086','Mascara','Sig','CENTRE-VILLE – SIG','07 53 62 43 66',35.528,-0.193),
    array('BDR-0087','Mascara','Mohammadia','CENTRE-VILLE – MOHAMMADIA','05 16 08 68 71',35.589,0.069),
    array('BDR-0088','Ouargla','Ouargla','AVENUE DE LA PALESTINE','05 81 07 34 94',31.953,5.328),
    array('BDR-0089','Ouargla','Hassi Messaoud','CENTRE-VILLE – HASSI MESSAOUD','07 78 08 97 54',31.68,6.07),
    array('BDR-0090','Ouargla','Rouissat','CENTRE-VILLE – ROUISSAT','07 38 76 60 57',31.95,5.35),
    array('BDR-0091','Oran','Oran','114 RUE LARBI BEN MHIDI 31000','07 08 52 99 90',35.6991,-0.6359),
    array('BDR-0092','Oran','Es Sénia','CENTRE-VILLE – ES SENIA','06 94 66 52 65',35.647,-0.623),
    array('BDR-0093','Oran','Arzew','CENTRE-VILLE – ARZEW','06 77 29 75 37',35.85,-0.316),
    array('BDR-0094','El Bayadh','El Bayadh','5 RUE EL BEY BOUKHOBZA 32000','06 85 60 84 49',33.683,1.02),
    array('BDR-0095','El Bayadh','Bougtob','3 RUE DES MARTYRS 32200','07 20 71 67 18',34.05,2.985),
    array('BDR-0096','El Bayadh','Brézina','CENTRE-VILLE – BREZINA','07 22 68 99 67',33.1,1.26),
    array('BDR-0097','Illizi','Illizi','CENTRE VILLE 33000','07 94 53 94 74',26.4833,8.4667),
    array('BDR-0098','Illizi','In Amenas','CENTRE-VILLE – IN AMENAS','07 69 56 97 51',28.05,9.55),
    array('BDR-0099','Illizi','Debdeb','CENTRE-VILLE – DEBDEB','06 31 04 39 09',30.0,9.07),
    array('BDR-0100','Bordj Bou Arreridj','Bordj Bou Arreridj','RUE HADJ MOKRANI 34000','07 59 88 30 71',36.073,4.761),
    array('BDR-0101','Bordj Bou Arreridj','Bordj Zemoura','CENTRE-VILLE – BORDJ ZEMOURA','07 77 16 82 75',36.276,4.856),
    array('BDR-0102','Bordj Bou Arreridj','Ras El Oued','CENTRE-VILLE – RAS EL OUED','07 44 53 43 08',35.946,5.031),
    array('BDR-0103','Boumerdès','Boumerdès','CENTRE-VILLE – BOUMERDÈS','05 88 00 33 04',36.7048,3.4662),
    array('BDR-0104','Boumerdès','Dellys','CENTRE-VILLE – DELLYS','05 28 72 34 33',36.917,3.914),
    array('BDR-0105','Boumerdès','Bordj Menaïel','CENTRE-VILLE – BORDJ MENAÏEL','07 88 69 52 91',36.745,3.723),
    array('BDR-0106','El Tarf','El Tarf','CENTRE-VILLE – EL TARF','05 84 76 93 24',36.767,8.313),
    array('BDR-0107','El Tarf','El Kala','7 RUE DU SAHRA 36100','07 03 04 55 44',36.895,8.443),
    array('BDR-0108','El Tarf','Dréan','08 RUE BENBOUALI MED 36200','05 15 35 75 39',36.683,7.822),
    array('BDR-0109','Tindouf','Tindouf','CITE MOUSSANI 03700','05 53 51 21 43',27.6711,-8.1474),
    array('BDR-0110','Tindouf','Oum El Assel','CENTRE-VILLE – OUM EL ASSEL','05 64 57 37 61',28.916,-7.995),
    array('BDR-0111','Tindouf','Tindouf','CITE MOUSSANI 03700','07 21 45 04 86',27.6711,-8.1474),
    array('BDR-0112','Tissemsilt','Tissemsilt','RUE DE L’INDEPENDANCE 38000','06 67 69 19 26',35.607,1.81),
    array('BDR-0113','Tissemsilt','Theniet El Had','RUE DE L’DEPENDANCE 38200','06 70 71 54 60',35.871,2.028),
    array('BDR-0114','Tissemsilt','Bordj Bounaâma','CENTRE-VILLE – BORDJ BOUNAÂMA','06 07 75 02 97',35.766,1.59),
    array('BDR-0115','El Oued','El Oued','CITE DES 400 LOGTS 07000','07 19 01 67 65',33.3683,6.8675),
    array('BDR-0116','El Oued','Guemar','CITE DES 16 LOGTS N°20','07 33 82 66 05',33.493,6.799),
    array('BDR-0117','El Oued','Robbah','CENTRE-VILLE – ROBBAH','06 53 54 52 89',33.229,6.905),
    array('BDR-0118','Khenchela','Khenchela','RUE BOUGOUFA EL HACHEMI 40000','05 14 13 75 42',35.4358,7.1433),
    array('BDR-0119','Khenchela','Kaïs','CITE 50 LOGEMENTS 40200','06 53 86 46 86',35.5,6.924),
    array('BDR-0120','Khenchela','Babar','CENTRE-VILLE – BABAR','06 96 85 16 63',35.17,7.1),
    array('BDR-0121','Souk Ahras','Souk Ahras','CENTRE-VILLE – SOUK AHRAS','06 53 66 84 64',36.2864,7.9511),
    array('BDR-0122','Souk Ahras','Sedrata','CENTRE-VILLE – SEDRATA','05 62 70 48 57',36.128,7.533),
    array('BDR-0123','Souk Ahras','M\'daourouch','CENTRE-VILLE – M’DAOUROUCH','05 35 75 10 26',36.076,7.564),
    array('BDR-0124','Tipaza','Tipaza','CENTRE-VILLE – TIPAZA','06 33 43 83 90',36.5897,2.447),
    array('BDR-0125','Tipaza','Cherchell','CENTRE-VILLE – CHERCHELL','07 55 44 82 44',36.607,2.19),
    array('BDR-0126','Tipaza','Koléa','RUE DES FRERES EL HACHMI – KOLEA','07 32 62 86 84',36.638,2.768),
    array('BDR-0127','Mila','Mila','CITE DES 500 LOGEMENTS MILA 43000','07 74 55 01 53',36.45,6.264),
    array('BDR-0128','Mila','Ferdjioua','30 RUE SALAH DJEBIR 43301','07 42 07 07 41',36.17,5.945),
    array('BDR-0129','Mila','Chelghoum Laïd','RUE KHELIFI ABDERAHMANE 43200','06 40 70 20 15',36.16,6.166),
    array('BDR-0130','Aïn Defla','Aïn Defla','CENTRE-VILLE – AÏN DEFLA','07 87 01 66 74',36.2641,1.968),
    array('BDR-0131','Aïn Defla','Khemis Miliana','CENTRE-VILLE – KHEMIS MILIANA','05 24 28 62 74',36.261,2.22),
    array('BDR-0132','Aïn Defla','Miliana','CENTRE-VILLE – MILIANA','06 36 93 89 98',36.305,2.24),
    array('BDR-0133','Naâma','Naâma','CENTRE-VILLE – NAÂMA','07 79 09 25 81',33.2667,-0.3167),
    array('BDR-0134','Naâma','Mécheria','RUE BEN DAHOU TAHAR 45100','07 14 97 64 07',33.55,-0.283),
    array('BDR-0135','Naâma','Aïn Sefra','24 RUE CHAHID RADJAA MOHAMED 45200','05 90 08 00 55',32.75,-0.5),
    array('BDR-0136','Aïn Témouchent','Aïn Témouchent','CENTRE-VILLE – AÏN TÉMOUCHENT','07 27 97 39 17',35.2975,-1.1404),
    array('BDR-0137','Aïn Témouchent','Béni Saf','CENTRE-VILLE – BÉNI SAF','06 17 10 66 58',35.3,-1.383),
    array('BDR-0138','Aïn Témouchent','Hammam Bou Hadjar','CENTRE-VILLE – HAMMAM BOU HADJAR','05 54 48 17 05',35.38,-0.97),
    array('BDR-0139','Ghardaïa','Ghardaïa','AVENUE DU 1er NOVEMBRE 47000','07 46 93 84 42',32.4909,3.6735),
    array('BDR-0140','Ghardaïa','Berriane','CENTRE-VILLE – BERRIANE','06 54 44 57 65',32.826,3.766),
    array('BDR-0141','Ghardaïa','El Guerrara','CER BP 99 GUERRARA 47110','07 81 87 74 70',32.79,4.49),
    array('BDR-0142','Relizane','Relizane','RUE CHEIKH LARBI TEBESSI 48000','05 57 85 40 00',35.737,0.555),
    array('BDR-0143','Relizane','Mazouna','CENTRE-VILLE – MAZOUNA','05 62 71 82 22',36.123,0.88),
    array('BDR-0144','Relizane','Zemmoura','CITE DES 100 LOGEMENTS 48155','06 19 54 06 22',35.72,0.755),
    array('BDR-0145','Timimoun','Timimoun','ROUTE DE CHEMORA','05 70 69 37 58',29.263,0.23),
    array('BDR-0146','Timimoun','Aougrout','CENTRE-VILLE – AOUGROUT','06 83 09 75 68',28.86,0.25),
    array('BDR-0147','Timimoun','Talmine','CENTRE-VILLE – TALMINE','07 37 66 20 06',29.4,-0.39),
    array('BDR-0148','Bordj Badji Mokhtar','Bordj Badji Mokhtar','CENTRE-VILLE – BORDJ BADJI MOKHTAR','07 33 35 93 90',21.328,0.955),
    array('BDR-0149','Bordj Badji Mokhtar','Timiaouine','CENTRE-VILLE – TIMIAOUINE','07 19 72 78 23',20.95,1.18),
    array('BDR-0150','Bordj Badji Mokhtar','Bordj Badji Mokhtar','CENTRE-VILLE – BORDJ BADJI MOKHTAR','06 35 51 71 76',21.328,0.955),
    array('BDR-0151','Ouled Djellal','Ouled Djellal','ROUTE PRINCIPALE 07400','05 21 01 38 86',34.426,5.064),
    array('BDR-0152','Ouled Djellal','Sidi Khaled','CENTRE-VILLE – SIDI KHALED','05 74 02 29 68',34.387,4.986),
    array('BDR-0153','Ouled Djellal','Ras El Miad','CENTRE-VILLE – RAS EL MIAD','07 23 37 00 68',34.05,4.9),
    array('BDR-0154','Béni Abbès','Béni Abbès','AVENUE DU 20 AOUT 08300','07 18 44 12 17',30.132,-2.167),
    array('BDR-0155','Béni Abbès','El Ouata','CENTRE-VILLE – EL OUATA','07 30 19 83 53',30.25,-2.1),
    array('BDR-0156','Béni Abbès','Kerzaz','CENTRE-VILLE – KERZAZ','07 49 90 56 02',30.01,-2.89),
    array('BDR-0157','In Salah','In Salah','CENTRE-VILLE – IN SALAH','07 67 05 45 53',27.193,2.46),
    array('BDR-0158','In Salah','Foggaret Ezzaouia','CENTRE-VILLE – FOGGARET EZZAOUIA','05 10 57 24 29',27.57,1.15),
    array('BDR-0159','In Salah','In Ghar','CENTRE-VILLE – IN GHAR','05 31 43 31 57',27.1,1.43),
    array('BDR-0160','In Guezzam','In Guezzam','CENTRE-VILLE – IN GUEZZAM','07 39 53 97 74',19.566,5.77),
    array('BDR-0161','In Guezzam','Tin Zaouatine','CENTRE-VILLE – TIN ZAOUATINE','07 29 32 93 27',19.95,2.97),
    array('BDR-0162','In Guezzam','In Guezzam','CENTRE-VILLE – IN GUEZZAM','07 17 34 74 89',19.566,5.77),
    array('BDR-0163','Touggourt','Touggourt','RUE DE LA LIBERTE','06 70 07 33 28',33.105,6.059),
    array('BDR-0164','Touggourt','Temacine','CENTRE-VILLE – TEMACINE','06 32 44 10 17',33.006,6.007),
    array('BDR-0165','Touggourt','Meggarine','CENTRE-VILLE – MEGGARINE','05 13 55 77 08',33.19,6.09),
    array('BDR-0166','Djanet','Djanet','CITE TIN KHATMA','06 28 66 50 25',24.555,9.484),
    array('BDR-0167','Djanet','Bordj El Haouès','CENTRE-VILLE – BORDJ EL HAOUÈS','05 85 54 17 91',25.3,8.0),
    array('BDR-0168','Djanet','Djanet','CITE TIN KHATMA','06 66 13 35 02',24.555,9.484),
    array('BDR-0169','El M’Ghair','El M’Ghair','CENTRE-VILLE – EL M’GHAIR','07 79 55 82 06',33.95,5.925),
    array('BDR-0170','El M’Ghair','Djamaa','CENTRE-VILLE – DJAMAA','05 61 60 05 85',33.53,5.99),
    array('BDR-0171','El M’Ghair','Oum Touyour','CENTRE-VILLE – OUM TOUYOUR','07 48 24 28 60',34.06,6.15),
    array('BDR-0172','El Meniaa','El Meniaa','ROUTE UNITE AFRICAINE','07 15 85 94 54',30.58,2.88),
    array('BDR-0173','El Meniaa','Hassi Gara','CENTRE-VILLE – HASSI GARA','07 56 38 97 12',30.58,2.88),
    array('BDR-0174','El Meniaa','Hassi Fehal','CENTRE-VILLE – HASSI FEHAL','07 11 56 02 09',30.77,2.7)
  );
}

/*
 * V13 : synchronisation complète du réseau d'agences avec le fichier Excel validé.
 * Une seule source de vérité : 174 lignes, codes BDR-0001 à BDR-0174,
 * adresses, téléphones et coordonnées GPS fournis par le listing V13.
 * Les anciennes agences générées par V11-4 sont supprimées puis recréées.
 */
function bdr_v13_sync_agencies() {
  if (get_option('bdr_v13_agencies_imported') === '1') return;

  $old = get_posts(array(
    'post_type'=>'bdr_agence', 'post_status'=>'any', 'posts_per_page'=>-1,
    'fields'=>'ids'
  ));
  foreach ($old as $old_id) wp_delete_post($old_id, true);

  foreach (bdr_v13_agency_data() as $a) {
    list($code,$wilaya,$ville,$adresse,$tel,$lat,$lng)=$a;
    $post_id=wp_insert_post(array(
      'post_type'=>'bdr_agence',
      'post_title'=>'Agence BDR '.$ville.' — '.$code,
      'post_content'=>'Agence du réseau BDR.',
      'post_status'=>'publish'
    ));
    if ($post_id && !is_wp_error($post_id)) {
      update_post_meta($post_id,'_bdr_wilaya',$wilaya);
      update_post_meta($post_id,'_bdr_ville',$ville);
      update_post_meta($post_id,'_bdr_adresse',$adresse);
      update_post_meta($post_id,'_bdr_tel',$tel);
      update_post_meta($post_id,'_bdr_code',$code);
      update_post_meta($post_id,'_bdr_lat',number_format($lat,6,'.',''));
      update_post_meta($post_id,'_bdr_lng',number_format($lng,6,'.',''));
      update_post_meta($post_id,'_bdr_verified','1');
    }
  }

  update_option('bdr_v13_agencies_imported','1');
  update_option('bdr_v4_agencies_ready','1');
  update_option('bdr_v4_agency_addresses_v3','1');
  update_option('bdr_v11_agency_spacing_v1','1');
  update_option('bdr_v4_agency_phones_v3','1');
}
add_action('init','bdr_v13_sync_agencies',34);

/* ---------- Carte des agences : Leaflet + OpenStreetMap ---------- */

function bdr_v4_map_data(){
  $q=new WP_Query(array('post_type'=>'bdr_agence','posts_per_page'=>-1,'post_status'=>'publish')); $out=array();
  while($q->have_posts()){ $q->the_post(); $id=get_the_ID(); $lat=get_post_meta($id,'_bdr_lat',true); $lng=get_post_meta($id,'_bdr_lng',true); if($lat===''||$lng==='') continue;
    $out[]=array('id'=>$id,'title'=>get_the_title(),'wilaya'=>get_post_meta($id,'_bdr_wilaya',true),'ville'=>get_post_meta($id,'_bdr_ville',true),'adresse'=>get_post_meta($id,'_bdr_adresse',true),'tel'=>get_post_meta($id,'_bdr_tel',true),'code'=>get_post_meta($id,'_bdr_code',true),'lat'=>(float)$lat,'lng'=>(float)$lng,'url'=>get_permalink($id));
  } wp_reset_postdata(); return $out;
}

/* ---------- Exchange rates ---------- */






/* ---------- V11 : headers HTTP de sécurité ---------- */


/* ---------- V11 : création des pages de navigation manquantes ---------- */


/* ---------- V11 : nettoyage du contenu WordPress de démonstration ---------- */


/* ---------- V11 : recherche type Spotlight, sans collecte de données sensibles ---------- */
function bdr_v11_search_endpoint(){
  $q=sanitize_text_field(wp_unslash($_GET['q']??''));
  $lang=sanitize_key($_GET['lang']??'fr');
  if(!in_array($lang,array('ar','fr','en'),true))$lang='fr';
  if(mb_strlen($q)<2) wp_send_json_success(array());
  $out=array();
  if(function_exists('bdr_v11_catalog')){
    foreach(bdr_v11_catalog() as $slug=>$c){
      if($slug==='')continue;
      $title=$c[$lang]??$c['fr'];
      $hay=mb_strtolower($title.' '.$slug);
      if(mb_strpos($hay,mb_strtolower($q))!==false){
        $out[]=array('title'=>$title,'type'=>$lang==='ar'?'قسم':($lang==='en'?'Section':'Rubrique'),'excerpt'=>$lang==='ar'?'صفحة ومعلومات مرتبطة بهذا الموضوع.':($lang==='en'?'Page and information related to this topic.':'Page et informations liées à ce sujet.'),'url'=>esc_url_raw(function_exists('bdr_v11_url')?bdr_v11_url($slug,$lang):home_url('/'.$slug.'/')));
      }
      if(count($out)>=12)break;
    }
  }
  $posts=get_posts(array('post_type'=>array('page','bdr_actualite','bdr_agence'),'post_status'=>'publish','posts_per_page'=>12,'s'=>$q));
  foreach($posts as $post){
    $url=get_permalink($post);
    if(function_exists('bdr_v11_url') && $post->post_type==='page')$url=bdr_v11_url($post->post_name,$lang);
    $type = $post->post_type==='bdr_agence' ? ($lang==='ar' ? 'وكالة' : ($lang==='en' ? 'Branch' : 'Agence')) : ($lang==='ar' ? 'أخبار' : ($lang==='en' ? 'News' : 'Actualité')); $out[]=array('title'=>get_the_title($post),'type'=>$type,'excerpt'=>wp_trim_words(wp_strip_all_tags($post->post_excerpt?:$post->post_content),18),'url'=>esc_url_raw($url));
  }
  wp_send_json_success(array_slice($out,0,15));
}
add_action('wp_ajax_bdr_site_search','bdr_v11_search_endpoint');
add_action('wp_ajax_nopriv_bdr_site_search','bdr_v11_search_endpoint');

/* ---------- Customizer ---------- */


/* ---------- One-click starter content ---------- */






/* ---------- Real site navigation ---------- */


/* ---------- Create the site's real page structure ---------- */
function bdr_v4_create_page($title,$slug){
  $p=get_page_by_path($slug,OBJECT,'page');
  if($p) return $p->ID;
  return wp_insert_post(array('post_type'=>'page','post_title'=>$title,'post_name'=>$slug,'post_status'=>'publish','post_content'=>''));
}






/* ---------- V7: official exchange-rate importer (Bank of Algeria) ---------- */









/* ---------- V8 visual data: fixed exchange ticker and varied agency addresses ---------- */



// V8 : aucune actualisation distante automatique ni bouton d'importation n'est utilisé sur le front-end.

/* ---------- V7: richer banking navigation ---------- */



/* ---------- Actualités éditoriales BDR : contenu initial réaliste ---------- */
function bdr_v9_seed_editorial_news(){
  if(get_option('bdr_v9_editorial_news_v2')) return;
  $items=array(
    array(
      'title'=>'La BDR au service des territoires',
      'image'=>'news-1.webp',
      'content'=>'<p class="lead">La Banque de Développement Régional place la proximité, l’écoute et l’accompagnement des projets au cœur de son approche. À travers son réseau et ses services, elle entend rapprocher les solutions bancaires des réalités économiques locales.</p><h2>Une banque ancrée dans les territoires</h2><p>Chaque territoire possède ses propres dynamiques : activité commerciale, industrie, agriculture, services, immobilier ou création d’entreprise. Une relation bancaire de proximité permet de mieux comprendre ces besoins et d’orienter les clients vers les solutions adaptées à leur situation.</p><h2>Accompagner les particuliers</h2><p>Les projets personnels nécessitent souvent plusieurs étapes : ouverture d’un compte, moyens de paiement, épargne, financement d’un logement ou accompagnement d’un projet familial. La BDR structure ses services pour faciliter ces démarches et permettre un suivi clair du projet.</p><h2>Être aux côtés des professionnels et des entreprises</h2><p>Création d’activité, acquisition d’équipements, développement d’un point de vente, investissement ou besoin de trésorerie : les professionnels et les entreprises peuvent avoir besoin d’un interlocuteur capable de comprendre leur activité et leur calendrier.</p><h2>Le réseau et les services digitaux</h2><p>La présence territoriale est complétée par des services à distance permettant de consulter les informations utiles et de simplifier certaines opérations. Cette complémentarité entre proximité et digital constitue un élément essentiel de l’expérience bancaire.</p><div class="article-highlight"><strong>Notre engagement :</strong> favoriser une relation bancaire accessible, claire et orientée vers les projets qui contribuent au développement des territoires.</div>'
    ),
    array(
      'title'=>'Financer les projets des PME',
      'image'=>'news-2.webp',
      'content'=>'<p class="lead">Le financement d’une PME ne se résume pas à un montant : il doit être cohérent avec le projet, son calendrier, les ressources de l’entreprise et sa capacité de remboursement. La préparation du dossier constitue donc une étape essentielle.</p><h2>Financer l’investissement</h2><p>Acquisition de matériel, renouvellement d’équipements, extension de locaux, modernisation d’un atelier ou lancement d’une nouvelle activité : les investissements peuvent accompagner une phase de croissance ou répondre à un besoin de transformation.</p><h2>Répondre aux besoins d’exploitation</h2><p>Le cycle d’exploitation peut générer des décalages entre les encaissements et les décaissements. Stocks, délais clients, commandes importantes ou saisonnalité sont autant de paramètres à prendre en compte dans l’analyse du besoin de financement.</p><h2>Construire un dossier solide</h2><p>Un dossier de financement doit présenter clairement l’entreprise et son activité, l’objet du projet, son coût, le calendrier de réalisation et les ressources mobilisées. Des éléments financiers, devis, documents administratifs ou pièces techniques peuvent être demandés selon la nature du projet.</p><h2>Un accompagnement adapté au projet</h2><p>L’étude d’une demande de financement prend en compte les caractéristiques du projet et la situation de l’entreprise. L’objectif est de disposer d’une vision complète avant de définir les modalités de financement susceptibles d’être étudiées.</p><div class="article-highlight"><strong>À préparer :</strong> une présentation claire du projet, son budget prévisionnel, les justificatifs disponibles et les principaux éléments financiers de l’entreprise.</div>'
    ),
    array(
      'title'=>'Accompagner la transition verte',
      'image'=>'news-3.webp',
      'content'=>'<p class="lead">La transition énergétique et environnementale concerne aujourd’hui de nombreux secteurs d’activité. Pour une entreprise ou un professionnel, elle peut prendre la forme d’un programme progressif de modernisation des équipements, de réduction des consommations ou d’amélioration des performances.</p><h2>Identifier les postes prioritaires</h2><p>Équipements énergivores, éclairage, isolation, climatisation, production, mobilité ou gestion des ressources : la première étape consiste à identifier les postes sur lesquels une action peut produire un effet durable.</p><h2>Transformer une idée en projet</h2><p>Une démarche de transition gagne à être structurée autour d’objectifs précis : investissement envisagé, coût, calendrier, gains attendus, contraintes techniques et ressources disponibles. Cette vision permet de mieux apprécier les différentes étapes du projet.</p><h2>Moderniser progressivement</h2><p>La transition peut être conduite par phases. Une première opération peut concerner un équipement ou un bâtiment, avant d’être complétée par d’autres investissements. Cette approche permet d’inscrire les actions dans une trajectoire pluriannuelle.</p><h2>Financer les investissements</h2><p>Selon la nature du projet et le profil du demandeur, différents besoins de financement peuvent être étudiés. Les modalités dépendent notamment du montant de l’investissement, de sa durée et de la situation financière du porteur de projet.</p><div class="article-highlight"><strong>Une démarche concrète :</strong> identifier les économies possibles, chiffrer les investissements et construire un calendrier réaliste de mise en œuvre.</div>'
    )
  );
  foreach($items as $item){
    $post=bdr_v15_find_by_title($item['title'],'bdr_actualite');
    if(!$post){
      $post_id=wp_insert_post(array('post_type'=>'bdr_actualite','post_title'=>$item['title'],'post_content'=>$item['content'],'post_status'=>'publish'));
    } else {
      $post_id=$post->ID;
      wp_update_post(array('ID'=>$post_id,'post_content'=>$item['content'],'post_status'=>'publish'));
    }
    if(!$post_id || is_wp_error($post_id)) continue;
    $file=get_theme_file_path('/assets/images/'.$item['image']);
    if(!file_exists($file)) continue;
    require_once ABSPATH.'wp-admin/includes/file.php';
    require_once ABSPATH.'wp-admin/includes/media.php';
    require_once ABSPATH.'wp-admin/includes/image.php';
    $old=get_post_thumbnail_id($post_id);
    $old_file=$old ? get_attached_file($old) : '';
    if(!$old || !$old_file || basename($old_file)!==basename($file)){
      $uploads=wp_upload_dir();
      $dest=trailingslashit($uploads['path']).basename($file);
      if(!file_exists($dest)) @copy($file,$dest);
      if(file_exists($dest)){
        $filetype=wp_check_filetype(basename($dest),null);
        $attachment=array('post_mime_type'=>$filetype['type'],'post_title'=>sanitize_text_field($item['title']),'post_content'=>'','post_status'=>'inherit');
        $attach_id=wp_insert_attachment($attachment,$dest,$post_id);
        if($attach_id && !is_wp_error($attach_id)){
          $meta=wp_generate_attachment_metadata($attach_id,$dest);
          wp_update_attachment_metadata($attach_id,$meta);
          set_post_thumbnail($post_id,$attach_id);
        }
      }
    }
  }
  update_option('bdr_v9_editorial_news_v2',1);
}
add_action('init','bdr_v9_seed_editorial_news',50);



/* ==========================================================
 * BDR V10 — SEO / Indexation / Structured data
 * ========================================================== */
function bdr_v10_seo_plugin_active(){
  return class_exists('WPSEO_Options') || class_exists('RankMath\\Core') || defined('AIOSEO_VERSION') || defined('SEOPRESS_VERSION');
}
function bdr_v10_page_title_map(){
  return array(
    ''=>'BDR – Banque de Développement Régional | Algérie','la-banque'=>'La Banque | BDR – Banque de Développement Régional','gouvernance'=>'Gouvernance | BDR – Banque de Développement Régional','engagements'=>'Nos engagements | BDR – Banque de Développement Régional',
    'particuliers'=>'Banque pour particuliers en Algérie | BDR','comptes-bancaires'=>'Comptes bancaires | BDR – Banque de Développement Régional','comptes-cartes'=>'Comptes & cartes bancaires | BDR','compte-cheque'=>'Compte courant | BDR – Banque de Développement Régional','operations-quotidiennes'=>'Opérations quotidiennes | Compte courant BDR','compte-devise'=>'Compte en devises | BDR – Banque de Développement Régional',
    'cartes-bancaires'=>'Cartes bancaires | BDR – Banque de Développement Régional','carte-cib'=>'Carte CIB | BDR – Banque de Développement Régional','carte-rahati'=>'Carte Rahati | BDR – Banque de Développement Régional','carte-internationale'=>'Carte bancaire internationale | BDR','carte-visa'=>'Carte Visa | BDR – Banque de Développement Régional',
    'credits'=>'Crédits et financements | BDR – Banque de Développement Régional','credit-immobilier'=>'Crédit immobilier en Algérie | BDR','credit-consommation'=>'Crédit à la consommation | BDR','credit-auto'=>'Crédit automobile | BDR','epargne'=>'Épargne | BDR – Banque de Développement Régional','epargne-disponible'=>'Épargne disponible | BDR','epargne-projet'=>'Épargne projet | BDR','epargne-jeune'=>'Épargne jeune | BDR','placements'=>'Placements | BDR – Banque de Développement Régional','depot-terme'=>'Dépôt à terme | BDR','bons-caisse'=>'Bons de caisse | BDR',
    'entreprises'=>'Banque pour entreprises et PME | BDR','entreprises-comptes-bancaires'=>'Comptes bancaires entreprises | BDR','carte-affaires'=>'Carte affaires | BDR','placements-entreprises'=>'Placements entreprises | BDR','financement'=>'Financement des entreprises | BDR','financement-investissement'=>'Financement des investissements | BDR','financement-exploitation'=>'Financement de l’exploitation | BDR','financement-pme-pmi'=>'Financement PME / PMI | BDR','commerce-exterieur'=>'Commerce extérieur | BDR – Banque de Développement Régional','domiciliation'=>'Domiciliation bancaire | BDR','credit-documentaire'=>'Crédit documentaire | BDR','remise-documentaire'=>'Remise documentaire | BDR','garanties-internationales'=>'Garanties internationales | BDR','transfert-libre'=>'Transfert libre | BDR',
    'agriculture'=>'Agriculture & territoires | BDR','financement-agriculture'=>'Financement agricole | BDR','financement-industrie'=>'Financement de l’industrie | BDR','financement-peche-aquaculture'=>'Financement pêche & aquaculture | BDR','finance-islamique'=>'Finance islamique | BDR – Banque de Développement Régional','finance-islamique-comptes'=>'Comptes & épargne – Finance islamique | BDR','livrets-epargne'=>'Livrets d’épargne | BDR','mourabaha'=>'Mourabaha | Finance islamique BDR','mourabaha-consommation'=>'Mourabaha consommation | BDR','mourabaha-travaux'=>'Mourabaha travaux | BDR','mourabaha-equipements'=>'Mourabaha équipements | BDR','mourabaha-agriculture'=>'Mourabaha agriculture | BDR','ijara'=>'Ijara | Finance islamique BDR','ijara-materiel-roulant'=>'Ijara matériel roulant | BDR','ijara-medical'=>'Ijara médical | BDR','ijara-travaux-publics'=>'Ijara travaux publics | BDR',
    'algeriens-residents-etranger'=>'Algériens résidents à l’étranger | BDR','institutionnels'=>'Institutionnels | BDR – Banque de Développement Régional','banque-en-ligne'=>'Banque en ligne | BDR – Banque de Développement Régional','services-distance'=>'Services bancaires à distance | BDR','alertes-sms'=>'Alertes SMS | BDR','location-coffre'=>'Location de coffre | BDR','securite-digitale'=>'Sécurité digitale | BDR','taux-de-change'=>'Taux de change | BDR – Banque de Développement Régional','agences'=>'Agences BDR en Algérie | Trouver une agence','actualites'=>'Actualités | Banque de Développement Régional','ouvrir-un-compte'=>'Ouvrir un compte | BDR – Banque de Développement Régional','contact'=>'Contact | BDR – Banque de Développement Régional','recrutement'=>'Recrutement | BDR – Banque de Développement Régional','mentions-legales'=>'Mentions légales | BDR','donnees-personnelles'=>'Données personnelles | BDR','plan-du-site'=>'Plan du site | BDR'
  );
}
function bdr_v10_description_map(){
  return array(
    ''=>'Banque de Développement Régional (BDR) : découvrez nos solutions pour particuliers, entreprises et territoires en Algérie, nos crédits, comptes, cartes, épargne, services en ligne et agences.',
    'particuliers'=>'Découvrez les solutions bancaires BDR pour les particuliers : comptes, cartes, crédits, épargne, placements et services bancaires à distance.',
    'entreprises'=>'La BDR accompagne les entreprises et PME avec des solutions de financement, comptes, placements, commerce extérieur et services bancaires adaptés.',
    'credits'=>'Découvrez les solutions de crédit et de financement BDR pour vos projets personnels, immobiliers et automobiles, avec les informations utiles pour préparer votre démarche.',
    'credit-immobilier'=>'Crédit immobilier BDR : découvrez les projets concernés, les caractéristiques du financement, les documents à préparer et les étapes de votre demande.',
    'credit-consommation'=>'Crédit à la consommation BDR : informations sur le financement de projets personnels, les conditions, les documents et les étapes de la démarche.',
    'credit-auto'=>'Crédit automobile BDR : découvrez les informations utiles pour préparer le financement de votre projet automobile.',
    'epargne'=>'Découvrez les solutions d’épargne BDR pour constituer une réserve, préparer un projet ou organiser votre épargne selon vos besoins.',
    'cartes-bancaires'=>'Découvrez les cartes bancaires BDR, leurs usages, leurs services et les informations à connaître avant votre demande.',
    'finance-islamique'=>'Découvrez les solutions de finance islamique proposées par la BDR : comptes, Mourabaha, Ijara et solutions de financement.',
    'banque-en-ligne'=>'Découvrez BDR-NET et les services bancaires à distance pour consulter et gérer vos services bancaires selon les fonctionnalités disponibles.',
    'agences'=>'Trouvez une agence BDR, consultez les coordonnées disponibles et localisez votre point de contact dans les wilayas couvertes par le réseau.',
    'actualites'=>'Retrouvez les informations, articles et éclairages de la Banque de Développement Régional sur ses activités et ses univers de services.',
    'ouvrir-un-compte'=>'Préparez votre démarche pour ouvrir un compte BDR : choix du compte, justificatifs et étapes avant la finalisation en agence.',
    'contact'=>'Contactez la BDR pour une demande d’information sur un produit, une agence ou une démarche. N’envoyez jamais de données d’authentification bancaire.'
  );
}


function bdr_v10_document_title($title){
  if(bdr_v10_seo_plugin_active()) return $title;
  if(is_front_page()) return 'BDR – Banque de Développement Régional | Algérie';
  if(is_singular('bdr_actualite')) return get_the_title().' | Actualités BDR';
  if(is_singular('bdr_agence')) return get_the_title().' | Agence BDR';
  $slug=is_singular()?get_post_field('post_name',get_queried_object_id()):''; $map=bdr_v10_page_title_map();
  return isset($map[$slug])?$map[$slug]:$title;
}
add_filter('pre_get_document_title','bdr_v10_document_title',20);




function bdr_v10_sitemap_post_types($post_types){
  // V15 : les 174 fiches agence (contenu mince) et le type « offre » inutilisé sortent du sitemap.
  unset($post_types['bdr_agence'],$post_types['bdr_offre']);
  if(isset($post_types['bdr_actualite'])) $post_types['bdr_actualite']->public=true;
  return $post_types;
}
add_filter('wp_sitemaps_post_types','bdr_v10_sitemap_post_types',20);

function bdr_v10_robots_txt($output,$public){ if(!$public) return $output; return "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nSitemap: ".home_url('/wp-sitemap.xml')."\n"; }
add_filter('robots_txt','bdr_v10_robots_txt',20,2);
function bdr_v10_attachment_alt($attr,$attachment){ if(!empty($attr['alt'])) return $attr; $title=get_the_title($attachment->ID); if($title) $attr['alt']=$title.' – BDR'; return $attr; }
add_filter('wp_get_attachment_image_attributes','bdr_v10_attachment_alt',20,2);
function bdr_v10_refresh_news_media(){
  if(get_option('bdr_v10_news_media_v1')) return;
  $items=array('La BDR au service des territoires'=>'news-1.webp','Financer les projets des PME'=>'news-2.webp','Accompagner la transition verte'=>'news-3.webp');
  require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/media.php'; require_once ABSPATH.'wp-admin/includes/image.php';
  foreach($items as $title=>$filename){
    $post=bdr_v15_find_by_title($title,'bdr_actualite'); if(!$post) continue; $file=get_theme_file_path('/assets/images/'.$filename); if(!file_exists($file)) continue;
    $old=get_post_thumbnail_id($post->ID); $old_file=$old?get_attached_file($old):''; if($old && $old_file && basename($old_file)===$filename) continue;
    $uploads=wp_upload_dir(); $dest=trailingslashit($uploads['path']).$filename; if(!file_exists($dest)) @copy($file,$dest);
    if(file_exists($dest)){ $type=wp_check_filetype($filename,null); $attachment=array('post_mime_type'=>$type['type'],'post_title'=>$title,'post_content'=>'','post_status'=>'inherit'); $aid=wp_insert_attachment($attachment,$dest,$post->ID); if($aid && !is_wp_error($aid)){ $meta=wp_generate_attachment_metadata($aid,$dest); wp_update_attachment_metadata($aid,$meta); set_post_thumbnail($post->ID,$aid); update_post_meta($aid,'_wp_attachment_image_alt',$title.' – Banque de Développement Régional'); } }
  }
  update_option('bdr_v10_news_media_v1',1);
}
add_action('init','bdr_v10_refresh_news_media',38);
function bdr_v10_admin_menu(){ add_theme_page('SEO BDR','SEO BDR','manage_options','bdr-v10-seo','bdr_v10_admin_page'); }
add_action('admin_menu','bdr_v10_admin_menu',50);
function bdr_v10_admin_page(){
  if(!current_user_can('manage_options')) return; $site=home_url('/');
  echo '<div class="wrap"><h1>BDR V10 — SEO</h1><p>Fondations SEO intégrées au thème : titres, meta descriptions, canoniques, Open Graph, données structurées, robots.txt et préparation du sitemap.</p><h2>URLs à vérifier</h2><table class="widefat striped" style="max-width:900px"><tbody>';
  foreach(array('Accueil'=>$site,'Sitemap'=>$site.'wp-sitemap.xml','Robots.txt'=>$site.'robots.txt','Actualités'=>$site.'actualites/') as $label=>$url) echo '<tr><th>'.esc_html($label).'</th><td><a href="'.esc_url($url).'" target="_blank" rel="noopener">'.esc_html($url).'</a></td></tr>';
  echo '</tbody></table><h2>Après mise en ligne</h2><ol><li>Ajouter le domaine dans Google Search Console.</li><li>Envoyer <code>wp-sitemap.xml</code> dans le rapport Sitemaps.</li><li>Inspecter l’accueil et quelques pages produits avec l’outil d’inspection d’URL.</li><li>Tester les données structurées et corriger les éventuelles erreurs.</li></ol><p><strong>Important :</strong> le thème ne collecte aucun mot de passe, PIN, OTP ou identifiant bancaire.</p></div>';
}


/* ========================= V11.4 – STABLE MULTILINGUAL ROUTING ========================= */
require_once get_template_directory() . '/inc/bdr-v11-languages.php';

function bdr_v11_add_rewrites(){
  add_rewrite_rule('^(fr|en|ar)/?$', 'index.php?bdr_lang=$matches[1]&bdr_front=1', 'top');
  add_rewrite_rule('^(fr|en|ar)/(.+)/?$', 'index.php?pagename=$matches[2]&bdr_lang=$matches[1]', 'top');
}
add_action('init','bdr_v11_add_rewrites',2);

function bdr_v11_query_vars($vars){
  $vars[]='bdr_lang'; $vars[]='bdr_front'; return $vars;
}
add_filter('query_vars','bdr_v11_query_vars');

function bdr_v11_template_include($template){
  if(get_query_var('bdr_front')){ status_header(200); return get_template_directory().'/front-page.php'; }
  return $template;
}
add_filter('template_include','bdr_v11_template_include',50);

function bdr_v11_flush_rules(){
  if(get_option('bdr_v11_4_rules')!=='1'){ flush_rewrite_rules(false); update_option('bdr_v11_4_rules','1'); }
}
add_action('init','bdr_v11_flush_rules',99);

function bdr_v11_redirect_canonical($redirect,$requested){
  if(get_query_var('bdr_lang') || get_query_var('bdr_front')) return false;
  return $redirect;
}
add_filter('redirect_canonical','bdr_v11_redirect_canonical',10,2);

function bdr_v11_lang_attr($output){
  $lang=bdr_v11_lang();
  return $lang==='ar' ? 'lang="ar" dir="rtl"' : ($lang==='en' ? 'lang="en" dir="ltr"' : 'lang="fr" dir="ltr"');
}
add_filter('language_attributes','bdr_v11_lang_attr',30);

function bdr_v11_body_class($classes){
  $classes[]='bdr-lang-'.bdr_v11_lang();
  if(get_query_var('bdr_lang')) $classes[]='bdr-language-route';
  return $classes;
}
add_filter('body_class','bdr_v11_body_class',20);

function bdr_v11_document_title($title){
  if(!get_query_var('bdr_lang') && !get_query_var('bdr_front') && !is_front_page()) return $title;
  $lang=bdr_v11_lang(); $slug=bdr_v11_get_current_slug();
  if($slug==='') return $lang==='ar'?'BDR – بنك التنمية الجهوية | الجزائر':($lang==='en'?'BDR – Regional Development Bank | Algeria':'BDR – Banque de Développement Régional | Algérie');
  return bdr_v11_title($slug,$lang).' | BDR';
}
add_filter('pre_get_document_title','bdr_v11_document_title',100);



function bdr_v11_get_current_slug(){
  if(get_query_var('bdr_front')) return '';
  return sanitize_title(get_post_field('post_name', get_queried_object_id()));
}

function bdr_v11_localize_pages($pages,$lang){
  foreach($pages as $slug=>$fr){
    if(!isset(bdr_v11_catalog()[$slug])) continue;
    $p=bdr_v11_profile($slug,$lang);
    $d=array(array($p['eyebrow'],$p['title'],$p['intro']));
    foreach($p['features'] as $f){ $d[]=array($f[0],$f[1],$f[2]??''); }
    $pages[$slug]=$d;
  }
  return $pages;
}

function bdr_v11_localize_rich_pages($rich,$lang){
  foreach($rich as $slug=>$d){
    if(!isset(bdr_v11_catalog()[$slug])) continue;
    $p=bdr_v11_profile($slug,$lang);
    $rich[$slug]['title']=$p['title'];
    $rich[$slug]['eyebrow']=$p['eyebrow'];
    $rich[$slug]['intro']=$p['intro'];
    $rich[$slug]['audience']=bdr_v11_audience($slug,$lang);
    $rich[$slug]['features']=$p['features'];
    $rich[$slug]['for']=$p['audience'];
    $rich[$slug]['docs']=$p['docs'];
    $rich[$slug]['simulate']=$p['simulate'];
  }
  return $rich;
}

function bdr_v11_audience($slug,$lang){ $p=bdr_v11_profile($slug,$lang); return $p['audience']??''; }


/* ==========================================================================
 * BDR V15 — socle corrigé
 * - SEO unifié (une seule description / canonical / OG / JSON-LD par page)
 * - En-têtes de sécurité sans script inline (CSP stricte pour les visiteurs)
 * - Redirections des anciennes URL françaises, actualités et agences
 * - Initialisation unique et légère (plus de requêtes à chaque page vue)
 * ========================================================================== */
if (!defined('BDR_V15_VERSION')) define('BDR_V15_VERSION', '17.0.0');
require_once get_template_directory() . '/inc/bdr-v15-ui.php';
require_once get_template_directory() . '/inc/bdr-v17-seo.php';

function bdr_v15_find_by_title($title, $type = 'page') {
  $q = new WP_Query(array('post_type' => $type, 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 1, 'no_found_rows' => true, 'orderby' => 'ID', 'order' => 'ASC'));
  return $q->have_posts() ? $q->posts[0] : null;
}

function bdr_v15_setup() {
  add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
  add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'bdr_v15_setup');

/* ---------- Création unique des pages du catalogue (idempotente) ---------- */
function bdr_v15_seed_pages() {
  if (get_option('bdr_v15_seeded') === BDR_V15_VERSION) return;
  foreach (bdr_v11_catalog() as $slug => $row) {
    if ($slug === '') continue;
    if (!get_page_by_path($slug, OBJECT, 'page')) {
      wp_insert_post(array('post_type' => 'page', 'post_title' => $row['fr'], 'post_name' => $slug, 'post_status' => 'publish', 'post_content' => ''));
    }
  }
  // Nettoyage du contenu de démonstration WordPress (une seule fois).
  $sample = get_page_by_path('sample-page', OBJECT, 'page');
  if ($sample) wp_delete_post($sample->ID, true);
  $hello = bdr_v15_find_by_title('Hello world!', 'post');
  if ($hello) wp_delete_post($hello->ID, true);
  update_option('bdr_v15_seeded', BDR_V15_VERSION);
  flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'bdr_v15_seed_pages');
add_action('init', 'bdr_v15_seed_pages', 60);

/* ---------- Ressources ---------- */
function bdr_v15_is_agency_route() {
  if (is_page('agences')) return true;
  return function_exists('bdr_v11_slug') && bdr_v11_slug() === 'agences';
}
function bdr_v15_assets() {
  $v = BDR_V15_VERSION; $uri = get_template_directory_uri();
  wp_enqueue_style('bdr-style', get_stylesheet_uri(), array(), $v);
  wp_enqueue_script('bdr-main', $uri . '/assets/js/main.js', array(), $v, array('in_footer' => true, 'strategy' => 'defer'));
  if (bdr_v15_is_agency_route()) {
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', array('in_footer' => true, 'strategy' => 'defer'));
    wp_enqueue_script('bdr-agencies-map', $uri . '/assets/js/agencies-map.js', array('leaflet'), $v, array('in_footer' => true, 'strategy' => 'defer'));
  }
  // Styles de blocs inutiles : le thème est 100 % classique.
  wp_dequeue_style('wp-block-library'); wp_dequeue_style('wp-block-library-theme'); wp_dequeue_style('global-styles'); wp_dequeue_style('classic-theme-styles');
}
add_action('wp_enqueue_scripts', 'bdr_v15_assets', 100);

/** Données transmises au JS via des blocs JSON (non exécutés, donc compatibles avec une CSP sans 'unsafe-inline'). */
function bdr_v15_footer_config() {
  $lang = bdr_v11_lang();
  $cfg = array(
    'ajax' => admin_url('admin-ajax.php'), 'lang' => $lang, 'home' => bdr_v11_url('', $lang),
    'i18n' => array(
      'min'   => bdr_v15_t('Saisissez au moins deux caractères.', 'Type at least two characters.', 'اكتب كلمتين على الأقل للبحث.', $lang),
      'none'  => bdr_v15_t('Aucun résultat trouvé.', 'No results found.', 'لا توجد نتائج.', $lang),
      'busy'  => bdr_v15_t('Recherche…', 'Searching…', 'جارٍ البحث…', $lang),
      'fail'  => bdr_v15_t('Recherche indisponible pour le moment.', 'Search is unavailable right now.', 'البحث غير متاح حالياً.', $lang),
      'play'  => bdr_v15_t('Lecture automatique', 'Autoplay', 'تشغيل تلقائي', $lang),
      'pause' => bdr_v15_t('Mettre en pause', 'Pause', 'إيقاف مؤقت', $lang),
    ),
  );
  if (function_exists('bdr_v16_login_js_config') && ($lg = bdr_v16_login_js_config($lang))) $cfg['login'] = $lg;
  $flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
  echo '<script type="application/json" id="bdr-config">' . wp_json_encode($cfg, $flags) . '</script>' . "\n";
  if (bdr_v15_is_agency_route()) {
    echo '<script type="application/json" id="bdr-agencies">' . wp_json_encode(bdr_v4_map_data(), $flags) . '</script>' . "\n";
  }
}
add_action('wp_footer', 'bdr_v15_footer_config', 5);

/* ---------- Nettoyage de l'en-tête WordPress ---------- */
function bdr_v15_cleanup_head() {
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');
  remove_action('admin_print_scripts', 'print_emoji_detection_script');
  remove_action('admin_print_styles', 'print_emoji_styles');
  remove_filter('the_content_feed', 'wp_staticize_emoji');
  remove_filter('comment_text_rss', 'wp_staticize_emoji');
  remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'wp_shortlink_wp_head');
  remove_action('template_redirect', 'wp_shortlink_header', 11);
  remove_action('wp_head', 'rest_output_link_wp_head');
  remove_action('template_redirect', 'rest_output_link_header', 11);
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('wp_head', 'feed_links_extra', 3);
}
add_action('init', 'bdr_v15_cleanup_head', 1);
add_filter('wp_speculation_rules_configuration', '__return_null'); // bloc inline bloqué par la CSP → erreur console sur chaque page
add_filter('emoji_svg_url', '__return_false');
add_filter('the_generator', '__return_empty_string');

/* ---------- En-têtes de sécurité ---------- */
function bdr_v15_security_headers($headers) {
  if (is_admin()) return $headers;
  $logged = function_exists('is_user_logged_in') && is_user_logged_in(); // la barre d'admin WP utilise des scripts inline
  $script = $logged ? "'self' 'unsafe-inline' https://unpkg.com" : "'self' https://unpkg.com";
  $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
  $headers['X-Content-Type-Options'] = 'nosniff';
  $headers['X-Frame-Options'] = 'SAMEORIGIN';
  $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
  $headers['Permissions-Policy'] = 'camera=(), microphone=(), geolocation=(), payment=(), usb=()';
  $headers['Cross-Origin-Opener-Policy'] = 'same-origin-allow-popups';
  $headers['Cross-Origin-Resource-Policy'] = 'same-origin';
  // Page « Banque en ligne » : le formulaire peut être envoyé au portail officiel BDR-NET (et à lui seul).
  $form_action = "'self'";
  if (function_exists('bdr_v16_is_online_route') && bdr_v16_is_online_route()) {
    $lc = bdr_v16_login_cfg();
    foreach (array($lc['post'], $lc['portal']) as $target) { $o = bdr_v16_origin($target); if ($o && strpos($form_action, $o) === false) $form_action .= ' ' . $o; }
  }
  $headers['Content-Security-Policy'] = "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; frame-src 'none'; form-action $form_action; "
    . "script-src $script; style-src 'self' 'unsafe-inline' https://unpkg.com; "
    . "img-src 'self' data: https://*.tile.openstreetmap.org https://unpkg.com; font-src 'self' data:; connect-src 'self'; upgrade-insecure-requests";
  return $headers;
}
add_filter('wp_headers', 'bdr_v15_security_headers');
add_action('send_headers', function () { header_remove('X-Powered-By'); });

/* ---------- Redirections : une seule URL par contenu ---------- */
function bdr_v15_redirects() {
  if (is_admin() || wp_doing_ajax()) return;
  // Anciennes pages françaises sans préfixe (/credits/) → /fr/credits/ : supprime le contenu dupliqué.
  if (is_page() && !get_query_var('bdr_lang') && !get_query_var('bdr_front') && !is_front_page()) {
    $slug = get_post_field('post_name', get_queried_object_id());
    $cat = bdr_v11_catalog();
    if ($slug !== '' && isset($cat[$slug])) { wp_safe_redirect(bdr_v11_url($slug, 'fr'), 301); exit; }
  }
  // Ancienne archive des actualités → page « Actualités » (liste gérée par le thème).
  if (is_post_type_archive('bdr_actualite')) { wp_safe_redirect(bdr_v11_url('actualites', 'fr'), 301); exit; }
  if (is_post_type_archive('bdr_agence')) { wp_safe_redirect(bdr_v11_url('agences', 'fr'), 301); exit; }
}
add_action('template_redirect', 'bdr_v15_redirects', 1);

/* ---------- Indexation : fiches agence et pages techniques hors index ---------- */
function bdr_v15_robots($robots) {
  if (is_singular(array('bdr_agence', 'bdr_offre')) || is_404() || is_search() || is_attachment()) {
    $robots['noindex'] = true; $robots['follow'] = true; unset($robots['max-image-preview']);
  } else {
    $robots['max-image-preview'] = 'large';
  }
  return $robots;
}
add_filter('wp_robots', 'bdr_v15_robots');

/* ---------- SEO unifié ---------- */
function bdr_v15_desc_home($lang) {
  $d = array(
    'fr' => 'Banque de Développement Régional (BDR) : solutions pour particuliers, entreprises et territoires en Algérie — comptes, cartes, crédits, épargne, finance islamique, services en ligne et réseau d’agences.',
    'en' => 'Regional Development Bank (BDR): solutions for individuals, businesses and territories in Algeria — accounts, cards, loans, savings, Islamic finance, online services and a nationwide branch network.',
    'ar' => 'بنك التنمية الجهوية (BDR): حلول للأفراد والمؤسسات والأقاليم في الجزائر — حسابات وبطاقات وتمويلات وادخار ومالية إسلامية وخدمات عبر الإنترنت وشبكة وكالات.',
  );
  return $d[$lang];
}
function bdr_v15_page_context() {
  $c = array('lang' => 'fr', 'slug' => '', 'url' => home_url('/'), 'desc' => '', 'type' => 'website', 'image' => '', 'crumbs' => array(), 'article' => null);
  if (is_singular('bdr_actualite')) {
    $c['url'] = get_permalink(); $c['type'] = 'article';
    $ex = wp_strip_all_tags(get_the_excerpt());
    $c['desc'] = $ex ? wp_trim_words($ex, 30, '…') : bdr_v10_description_map()['actualites'];
    $c['image'] = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
    $c['crumbs'] = array(array('Accueil', bdr_v11_url('', 'fr')), array('Actualités', bdr_v11_url('actualites', 'fr')), array(wp_strip_all_tags(get_the_title()), get_permalink()));
    $c['article'] = array('headline' => wp_strip_all_tags(get_the_title()), 'published' => get_the_date('c'), 'modified' => get_the_modified_date('c'));
    return $c;
  }
  if (is_singular('bdr_agence')) {
    $id = get_the_ID(); $v = get_post_meta($id, '_bdr_ville', true); $w = get_post_meta($id, '_bdr_wilaya', true);
    $c['url'] = get_permalink();
    $c['desc'] = 'Agence BDR' . ($v ? ' à ' . $v : '') . ($w ? ' – ' . $w : '') . ' : adresse, téléphone et localisation sur la carte du réseau BDR.';
    return $c;
  }
  $lang = bdr_v11_lang(); $slug = function_exists('bdr_v11_get_current_slug') ? bdr_v11_get_current_slug() : '';
  $cat = bdr_v11_catalog();
  if ($slug !== '' && !isset($cat[$slug])) $slug = '';
  $c['lang'] = $lang; $c['slug'] = $slug; $c['url'] = bdr_v11_url($slug, $lang);
  $c['desc'] = bdr_v17_desc($slug, $lang);
  if ($slug !== '') {
    $chain = array(); $cur = $slug; $g = 0;
    while ($cur && $g++ < 8) { array_unshift($chain, $cur); $cur = bdr_v11_parent_slug($cur); }
    $c['crumbs'][] = array(bdr_v11_title('', $lang), bdr_v11_url('', $lang));
    foreach ($chain as $s) $c['crumbs'][] = array(bdr_v11_title($s, $lang), bdr_v11_url($s, $lang));
  }
  return $c;
}
function bdr_v15_head() {
  if (is_admin() || bdr_v10_seo_plugin_active()) return;
  $c = bdr_v15_page_context();
  $lang = $c['lang']; $url = esc_url($c['url']); $desc = esc_attr(wp_strip_all_tags($c['desc']));
  $title = esc_attr(wp_get_document_title());
  $og = bdr_v17_og_image($c['slug']);
  $img = $c['image'] ? $c['image'] : $og['url'];
  $og_w = $c['image'] ? 0 : $og['w']; $og_h = $c['image'] ? 0 : $og['h'];
  $og_alt = function_exists('bdr_v16_photo_alt') ? bdr_v16_photo_alt($og['photo'], $lang) : '';
  $locale = $lang === 'ar' ? 'ar_DZ' : ($lang === 'en' ? 'en_GB' : 'fr_FR');
  echo '<meta name="description" content="' . $desc . '">' . "\n";
  echo '<link rel="canonical" href="' . $url . '">' . "\n";
  if (!is_singular(array('bdr_actualite', 'bdr_agence', 'bdr_offre')) && !is_404()) {
    foreach (array('fr', 'en', 'ar') as $lc) echo '<link rel="alternate" hreflang="' . $lc . '" href="' . esc_url(bdr_v11_url($c['slug'], $lc)) . '">' . "\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url(bdr_v11_url($c['slug'], 'ar')) . '">' . "\n";
  }
  echo '<meta property="og:type" content="' . esc_attr($c['type']) . '">' . "\n";
  echo '<meta property="og:site_name" content="BDR – Banque de Développement Régional">' . "\n";
  echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
  echo '<meta property="og:title" content="' . $title . '">' . "\n";
  echo '<meta property="og:description" content="' . $desc . '">' . "\n";
  echo '<meta property="og:url" content="' . $url . '">' . "\n";
  echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
  if ($og_w) echo '<meta property="og:image:width" content="' . $og_w . '">' . "\n" . '<meta property="og:image:height" content="' . $og_h . '">' . "\n" . '<meta property="og:image:alt" content="' . esc_attr($og_alt) . '">' . "\n";
  $alts = array('ar' => 'ar_DZ', 'fr' => 'fr_FR', 'en' => 'en_GB');
  foreach ($alts as $lc => $loc) if ($lc !== $lang && !is_singular(array('bdr_actualite', 'bdr_agence', 'bdr_offre'))) echo '<meta property="og:locale:alternate" content="' . $loc . '">' . "\n";
  echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
  echo '<meta name="twitter:title" content="' . $title . '">' . "\n";
  echo '<meta name="twitter:description" content="' . $desc . '">' . "\n";
  echo '<meta name="twitter:image" content="' . esc_url($img) . '">' . "\n";
  echo '<meta name="theme-color" content="#062f36">' . "\n";

  $home = home_url('/'); $graph = array();
  $org = array('@type' => 'BankOrCreditUnion', '@id' => $home . '#organization', 'name' => 'Banque de Développement Régional', 'alternateName' => 'BDR', 'url' => $home,
    'logo' => array('@type' => 'ImageObject', 'url' => get_template_directory_uri() . '/assets/images/logo-bdr.png'), 'areaServed' => 'DZ', 'image' => get_template_directory_uri() . '/assets/images/og/headquarters.jpg', 'description' => bdr_v15_desc_home($lang),
    'contactPoint' => array(array('@type' => 'ContactPoint', 'contactType' => 'customer service', 'telephone' => bdr_v15_opt('phone_raw'), 'email' => bdr_v15_opt('email'), 'areaServed' => 'DZ', 'availableLanguage' => array('ar', 'fr', 'en'))),
    'email' => bdr_v15_opt('email'), 'telephone' => bdr_v15_opt('phone_raw'));
  $same = array_values(array_filter(array(bdr_v15_opt('facebook'), bdr_v15_opt('linkedin'), bdr_v15_opt('youtube'), bdr_v15_opt('instagram'))));
  if ($same) $org['sameAs'] = $same;
  $graph[] = $org;
  $graph[] = array('@type' => 'WebSite', '@id' => $home . '#website', 'url' => $home, 'name' => 'BDR – Banque de Développement Régional', 'publisher' => array('@id' => $home . '#organization'), 'inLanguage' => array('ar', 'fr', 'en'), 'alternateName' => array('BDR', 'Banque de Développement Régional', 'بنك التنمية الجهوية'));
  if ($c['article']) {
    $graph[] = array('@type' => 'Article', '@id' => $c['url'] . '#article', 'headline' => $c['article']['headline'], 'description' => $c['desc'], 'datePublished' => $c['article']['published'], 'dateModified' => $c['article']['modified'],
      'mainEntityOfPage' => array('@type' => 'WebPage', '@id' => $c['url']), 'author' => array('@id' => $home . '#organization'), 'publisher' => array('@id' => $home . '#organization'), 'image' => $c['image'] ? array($c['image']) : array($img), 'inLanguage' => 'fr');
  } else {
    $wp = array('@type' => bdr_v17_page_type($c['slug']), '@id' => $c['url'] . '#webpage', 'url' => $c['url'], 'name' => wp_get_document_title(), 'description' => wp_strip_all_tags($c['desc']), 'inLanguage' => $lang, 'isPartOf' => array('@id' => $home . '#website'), 'about' => array('@id' => $home . '#organization'),
      'primaryImageOfPage' => array('@type' => 'ImageObject', 'url' => $og['url'], 'width' => $og['w'], 'height' => $og['h'], 'caption' => $og_alt));
    if (count($c['crumbs']) > 1) $wp['breadcrumb'] = array('@id' => $c['url'] . '#breadcrumb');
    if (bdr_v17_is_product($c['slug'])) {
      $wp['mainEntity'] = array('@id' => $c['url'] . '#service');
      $graph[] = array('@type' => 'FinancialProduct', '@id' => $c['url'] . '#service', 'name' => bdr_v11_title($c['slug'], $lang), 'description' => wp_strip_all_tags($c['desc']), 'url' => $c['url'], 'inLanguage' => $lang,
        'provider' => array('@id' => $home . '#organization'), 'areaServed' => array('@type' => 'Country', 'name' => 'DZ'));
    }
    $graph[] = $wp;
  }
  if (count($c['crumbs']) > 1) {
    $items = array();
    foreach ($c['crumbs'] as $i => $cr) $items[] = array('@type' => 'ListItem', 'position' => $i + 1, 'name' => $cr[0], 'item' => $cr[1]);
    $graph[] = array('@type' => 'BreadcrumbList', '@id' => $c['url'] . '#breadcrumb', 'itemListElement' => $items);
  }
  if (is_singular('bdr_agence') && get_post_meta(get_the_ID(), '_bdr_verified', true) === '1') {
    $id = get_the_ID();
    $graph[] = array('@type' => 'BankOrCreditUnion', '@id' => get_permalink() . '#agency', 'name' => get_the_title(), 'url' => get_permalink(), 'telephone' => get_post_meta($id, '_bdr_tel', true),
      'address' => array('@type' => 'PostalAddress', 'streetAddress' => get_post_meta($id, '_bdr_adresse', true), 'addressLocality' => get_post_meta($id, '_bdr_ville', true), 'addressRegion' => get_post_meta($id, '_bdr_wilaya', true), 'addressCountry' => 'DZ'),
      'geo' => array('@type' => 'GeoCoordinates', 'latitude' => get_post_meta($id, '_bdr_lat', true), 'longitude' => get_post_meta($id, '_bdr_lng', true)), 'parentOrganization' => array('@id' => $home . '#organization'));
  }
  echo '<script type="application/ld+json">' . wp_json_encode(array('@context' => 'https://schema.org', '@graph' => $graph), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>' . "\n";
}
add_action('wp_head', 'bdr_v15_head', 2);

/* ---------- Actualités : liste localisée (la page /fr/actualites/ s'affichait vide) ---------- */
function bdr_v15_date($ts, $lang) {
  $months = array(
    'fr' => array('', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'),
    'en' => array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
    'ar' => array('', 'جانفي', 'فيفري', 'مارس', 'أفريل', 'ماي', 'جوان', 'جويلية', 'أوت', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'),
  );
  return date('j', $ts) . ' ' . $months[$lang][(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
function bdr_v15_render_news($lang, $limit = 12) {
  $q = new WP_Query(array('post_type' => 'bdr_actualite', 'posts_per_page' => $limit, 'no_found_rows' => true, 'post_status' => 'publish'));
  if (!$q->have_posts()) { echo '<div class="panel"><p>' . esc_html(bdr_v15_t('Aucune actualité publiée pour le moment.', 'No news published yet.', 'لا توجد أخبار منشورة حالياً.', $lang)) . '</p></div>'; return; }
  if ($lang !== 'fr') echo '<p class="news-lang-note">' . esc_html(bdr_v15_t('', 'Articles are currently published in French.', 'المقالات منشورة حالياً باللغة الفرنسية.', $lang)) . '</p>';
  echo '<div class="news-grid">';
  while ($q->have_posts()) {
    $q->the_post();
    echo '<article class="news-card"><a href="' . esc_url(get_permalink()) . '">';
    if (has_post_thumbnail()) echo '<div class="news-card-img">' . get_the_post_thumbnail(get_the_ID(), 'medium_large', array('loading' => 'lazy', 'alt' => esc_attr(get_the_title()))) . '</div>';
    echo '<div class="news-card-body"><time datetime="' . esc_attr(get_the_date('c')) . '">' . esc_html(bdr_v15_date(get_post_time('U'), $lang)) . '</time><h3>' . esc_html(get_the_title()) . '</h3><p>' . esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 26, '…')) . '</p><span class="card-link">' . esc_html(bdr_v15_t('Lire la suite', 'Read more', 'اقرأ المزيد', $lang)) . ' ' . bdr_v15_icon('arrow', 'ic ic-arrow') . '</span></div></a></article>';
  }
  echo '</div>';
  wp_reset_postdata();
}

/* Cours de change : voir inc/bdr-v16-fx.php (récupération quotidienne + administration). */
function bdr_v15_exchange_source() { return function_exists('bdr_v16_fx_source') ? bdr_v16_fx_source() : 'https://www.bank-of-algeria.dz/taux-de-change-journalier/'; }
require_once get_template_directory() . '/inc/bdr-v15-components.php';
require_once get_template_directory() . '/inc/bdr-v16-fx.php';
require_once get_template_directory() . '/inc/bdr-v16-login.php';
