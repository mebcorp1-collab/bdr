=== BDR Banque en ligne ===
Contributors: bdr
Tags: banque en ligne, espace client, messagerie, documents, dossiers, faq, adhésion
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later

Banque en ligne et espace client BDR-NET de www.bdr-dz.com : suivi des dossiers, dépôt de documents, messagerie sécurisée
avec le conseiller, FAQ, conseils de sécurité et demandes d'adhésion. Aucune opération bancaire.

== Description ==

Tout se gère depuis le menu « Banque en ligne » de l'administration :

* Dossiers clients : la banque ouvre un dossier, l'attribue à un client et à un conseiller, et lui donne un statut
  (Ouvert, En cours de traitement, En attente de documents, Clôturé). Un dossier clôturé passe en lecture seule pour le client.
* FAQ : questions / réponses affichées sur la page Banque en ligne (champ « Ordre » pour les trier).
* Demandes d'adhésion : demandes envoyées depuis le site. Le bouton « Créer l'accès espace client » crée le compte
  du client et son premier dossier, puis lui envoie un e-mail pour choisir son mot de passe.
* Réglages : bouton d'accès, applications mobiles, assistance, conseils de sécurité, formulaire d'adhésion et,
  dans la section « Espace client », la page de l'espace client, la taille maximale des documents et l'e-mail de notification.

Côté client, l'espace client (shortcode [bdr_espace_client]) permet de :
* se connecter avec les identifiants remis par la banque ;
* voir ses dossiers et leur statut ;
* échanger avec son conseiller (compteur de messages non lus) ;
* déposer et télécharger des documents (PDF, JPG, PNG).
Les notifications par e-mail ne contiennent aucune donnée personnelle : seulement un lien pour se connecter.

== Sécurité ==

* Pas d'inscription libre : seuls les comptes créés par la banque (rôle « Client BDR ») ont accès.
* Chaque client ne voit que ses propres dossiers. Les conseillers (rôle « Conseiller BDR ») et administrateurs voient tous les dossiers.
  Seuls les administrateurs peuvent créer des comptes clients et modifier les réglages.
* Les clients n'ont pas accès au tableau de bord WordPress ni à la barre d'administration.
* Documents : type vérifié sur le contenu réel du fichier, nom aléatoire sans extension, dossier privé,
  téléchargement uniquement via un lien signé après contrôle des droits.
* Blocage de 15 minutes après 5 échecs de connexion sur un même identifiant, ou 20 depuis une même adresse IP.
* Jetons anti-falsification (nonce) sur tous les formulaires, limitation du nombre d'envois.
* Les pages de l'espace client ne sont jamais mises en cache.
* Le formulaire d'adhésion ne demande jamais d'identifiant, de mot de passe, de numéro de compte ou de carte.

Recommandations indispensables pour un site bancaire :
1. Site entièrement en HTTPS.
2. Stocker les documents hors du site : ajouter dans wp-config.php
   define( 'BDR_EC_STORAGE_DIR', '/chemin/hors/du/site/bdr-documents' );
   (obligatoire si le serveur utilise Nginx, qui ignore les fichiers .htaccess).
3. Activer la double authentification (ex. extension « Two Factor » ou « Wordfence Login Security »).
4. Envoyer les e-mails via SMTP (WP Mail SMTP est déjà installé).
5. Sauvegardes régulières chiffrées de la base de données et du dossier de documents.
6. Faire valider le dispositif par le responsable sécurité / conformité (loi 18-07 sur les données personnelles).

== Shortcodes ==

* `[bdr_espace_client]` : espace client (connexion, dossiers, messagerie, documents).
* `[bdr_banque_en_ligne]` : page complète (accès à l'espace client, applications, sécurité, FAQ, adhésion, assistance).
* `[bdr_eb_bouton texte="Accéder à mon espace client"]` : bouton vers l'espace client.
* `[bdr_eb_applications]`, `[bdr_eb_securite]`, `[bdr_eb_faq]`, `[bdr_eb_adhesion]`, `[bdr_eb_assistance]`.

== Langues ==

L'extension est traduite en français (langue d'origine), en anglais (en_US) et en arabe (ar).
* La langue suit celle du site. Avec Polylang ou WPML, créez les pages (espace client, banque en ligne) dans chaque langue
  avec les mêmes shortcodes ; sélectionnez la page principale de l'espace client dans les réglages.
* En arabe, la mise en page passe automatiquement de droite à gauche.
* Les e-mails sont envoyés dans la langue du destinataire (champ « Langue » de son profil utilisateur).
* Textes saisis dans les réglages (texte du bouton, horaires, conseils de sécurité, message de confirmation) :
  Polylang > Langues > Traductions des chaînes (groupe « BDR Banque en ligne »), ou WPML > Traduction de chaînes.
* FAQ : traduisible comme un article avec Polylang ou WPML.
* Pour modifier une traduction : languages/bdr-banque-en-ligne-ar.po et -en_US.po (Poedit ou Loco Translate).

== Installation / mise à jour ==

1. Si l'ancienne extension « BDR Espace Client » ou « BDR-NET » est installée : désactivez-la puis supprimez-la.
   Ses dossiers, messages, documents et réglages sont repris automatiquement.
   (Tant qu'elle reste active, l'espace client de Banque en ligne reste éteint et un message s'affiche, pour éviter tout conflit.)
2. Extensions > Ajouter > Téléverser une extension : bdr-banque-en-ligne.zip, puis « Remplacer la version installée » et Activer.
3. Créez une page (ex. « BDR-NET » ou « Espace client ») contenant [bdr_espace_client].
4. Banque en ligne > Réglages, section « Espace client » : sélectionnez cette page.
5. Utilisateurs > Ajouter : créez vos conseillers avec le rôle « Conseiller BDR ».
   Les clients sont créés depuis les demandes d'adhésion, ou à la main avec le rôle « Client BDR ».
6. Banque en ligne > Dossiers clients > Nouveau dossier client : titre, client, conseiller, puis Publier.

Le conseiller répond aux clients depuis la page de l'espace client (bouton « Répondre » sur la fiche du dossier).

== Désinstallation ==

Seuls les réglages sont supprimés. FAQ, demandes, dossiers, messages, documents et comptes clients sont conservés.

== Changelog ==

= 2.0.0 =
* L'espace client (dossiers, messagerie, documents) est intégré à Banque en ligne : plus besoin d'extension séparée.
* Le bouton d'accès mène à la page de l'espace client du site (plus de portail externe à renseigner).
* Demandes d'adhésion : bouton « Créer l'accès espace client » (compte + premier dossier + e-mail de mot de passe).
* Les conseillers accèdent aux dossiers clients ; les réglages restent réservés aux administrateurs.
* Traductions anglaise et arabe de toute l'extension, mise en page droite à gauche.
* Compatibilité Polylang / WPML pour les textes des réglages et la FAQ.

= 1.0.0 =
* Première version.
