BDR Modern 16 — thème WordPress de la Banque de Développement Régional
=====================================================================

INSTALLATION (toujours sur un site de test d'abord)
1. Sauvegardez le site (fichiers + base) depuis o2switch / cPanel.
2. Apparence > Thèmes > Ajouter > Téléverser : bdr-modern-v16.zip, puis Activer.
3. Réglages > Permaliens > Enregistrer (régénère les adresses /fr/ /en/ /ar/).
4. Apparence > Personnaliser > « BDR – Coordonnées & liens » :
   - téléphone, e-mail, réseaux sociaux ;
   - BDR-NET : adresse de la page de connexion officielle (https) ;
   - BDR-NET : adresse d'envoi du formulaire + noms des champs (voir « Connexion client »).
5. Menu « Taux de change » (administration) : cliquer « Mettre à jour maintenant » et vérifier le message d'état.
6. Videz le cache (LiteSpeed) et testez : /, /fr/, /en/, /ar/, /fr/banque-en-ligne/, /fr/taux-de-change/, /fr/agences/.

CONNEXION CLIENT (page « Banque en ligne »)
- Les identifiants ne passent jamais par WordPress : le formulaire est envoyé directement au portail BDR-NET.
- Mode « direct » : renseigner « adresse d'envoi du formulaire » (action du formulaire de connexion BDR-NET) et les noms de champs
  (par défaut username / password). À obtenir auprès de l'éditeur de BDR-NET. Si le formulaire BDR-NET exige des jetons générés par son serveur
  (CSRF, etc.), utiliser le mode « portail ».
- Mode « portail » : seule l'adresse de la page de connexion est renseignée ; le visiteur saisit le CAPTCHA puis est redirigé vers BDR-NET.
- Sans aucune adresse : la page affiche un message neutre (aucun envoi possible).
- Le CAPTCHA est généré par le serveur (tracés SVG signés, 10 min, usage unique, limitation par IP). Il protège cette page ;
  BDR-NET doit garder ses propres contrôles (verrouillage, double authentification).

TAUX DE CHANGE (mise à jour quotidienne)
- Tâche WP-Cron quotidienne (≈ 9 h 05, heure d'Alger) : télécharge la page de la Banque d'Algérie et lit le tableau (codes ISO ou noms FR/AR,
  achat/vente ou cours unique, virgule ou point, unités ×100 ramenées à 1).
- En cas d'échec (site indisponible, page modifiée ou chargée par JavaScript) : derniers cours valides conservés ; état et bouton
  « Mettre à jour maintenant » dans Administration > Taux de change. Mode « manuel » disponible.
- Recommandé sur o2switch : une vraie tâche cron toutes les 15 min vers wp-cron.php (voir la page d'administration).

IMAGES
- assets/images/pages/<slug>.webp : une illustration originale par page (72) ; déposer un fichier du même nom (webp/jpg/png, 1600x900) la remplace par une photo.
- assets/images/panoramas/pano-*.webp : 6 panoramas 3200x1000 de la galerie d'accueil (mêmes noms pour les remplacer, + version -1600.webp).

STRUCTURE
header.php / footer.php         en-tête (tuile logo, bandeau sombre, méga-menu, bloc Espace client) et pied de page
front-page.php                  accueil (héros, accès rapides, mosaïque, panoramas, repères + cours, actualités)
page.php                        pages de contenu (FR/EN/AR)
inc/bdr-v16-fx.php              récupération quotidienne des cours + administration
inc/bdr-v16-login.php           page de connexion, CAPTCHA, AJAX
inc/bdr-v15-ui.php              navigation unique, icônes, images, statistiques
inc/bdr-v15-components.php      bannière de page, sections, cartes, FAQ…
inc/bdr-v11-languages.php       catalogue et textes trilingues
assets/js/main.js               menus, recherche, carrousel, galerie, connexion, simulateur (aucun script en ligne)

Version 16.0.0 — nouvelle identité visuelle « Abysse & Émeraude » ; remplace la 15.0.0.
