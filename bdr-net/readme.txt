=== BDR-NET ===
Contributors: bdr
Tags: espace client, messagerie, documents, dossiers, banque
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later

BDR-NET, l'espace client privé de la BDR : suivi des dossiers, dépôt de documents et messagerie sécurisée entre le client et son conseiller. Aucune opération bancaire.

== Description ==

* Dossiers clients : créés par la banque (menu « BDR-NET »), attribués à un client et à un conseiller, avec un statut
  (Ouvert, En cours de traitement, En attente de documents, Clôturé).
* Messagerie privée par dossier entre le client et la banque, avec compteur de messages non lus.
* Dépôt de documents (PDF, JPG, PNG) par le client et par la banque.
* Notifications par e-mail sans aucun contenu personnel : seulement un lien pour se connecter.

== Sécurité ==

* Pas d'inscription libre : seuls les comptes créés par la banque (rôle « Client BDR ») ont accès.
* Chaque client ne voit que ses propres dossiers. Les conseillers (rôle « Conseiller BDR ») et administrateurs voient tous les dossiers.
* Les clients n'ont pas accès au tableau de bord WordPress ni à la barre d'administration.
* Documents : type vérifié sur le contenu réel du fichier, nom aléatoire sans extension, dossier privé,
  téléchargement uniquement via un lien signé après contrôle des droits.
* Blocage de 15 minutes après 5 échecs de connexion sur un même identifiant, ou 20 depuis une même adresse IP.
* Jetons anti-falsification (nonce) sur tous les formulaires, limitation du nombre d'envois.
* Les pages de l'espace client ne sont jamais mises en cache.

Recommandations indispensables pour un site bancaire :
1. Site entièrement en HTTPS.
2. Stocker les documents hors du site : ajouter dans wp-config.php
   define( 'BDR_EC_STORAGE_DIR', '/chemin/hors/du/site/bdr-documents' );
   (obligatoire si le serveur utilise Nginx, qui ignore les fichiers .htaccess).
3. Activer la double authentification (ex. extension « Two Factor » ou « Wordfence Login Security »).
4. Envoyer les e-mails via SMTP (WP Mail SMTP est déjà installé).
5. Sauvegardes régulières chiffrées de la base de données et du dossier de documents.
6. Faire valider le dispositif par le responsable sécurité / conformité (loi 18-07 sur les données personnelles).

== Langues ==

L'extension est traduite en français (langue d'origine), en anglais (en_US) et en arabe (ar).
* La langue suit celle du site. Avec Polylang ou WPML, chaque version linguistique de la page BDR-NET
  s'affiche dans sa langue : créez la page dans chaque langue avec le shortcode [bdr_espace_client]
  et sélectionnez la page principale dans les réglages.
* En arabe, la mise en page passe automatiquement de droite à gauche.
* Messages, titres et noms de fichiers gardent leur propre sens d'écriture (un message en français reste lisible dans la page arabe).
* Les e-mails sont envoyés dans la langue du destinataire (champ « Langue » de son profil utilisateur).
* Pour modifier une traduction : fichiers languages/bdr-net-ar.po et -en_US.po (éditables avec Poedit ou Loco Translate).

== Installation ==

BDR-NET est une extension autonome : elle n'a pas besoin de « BDR Banque en ligne » ni d'aucune autre extension BDR.

1. Extensions > Ajouter > Téléverser une extension, choisissez bdr-net.zip, puis Activer.
2. Créez une page (ex. « BDR-NET ») contenant le shortcode [bdr_espace_client].
3. BDR-NET > Réglages : sélectionnez cette page.
4. Utilisateurs > Ajouter : créez chaque client avec le rôle « Client BDR » (il reçoit un e-mail pour choisir son mot de passe).
   Créez vos conseillers avec le rôle « Conseiller BDR ».
5. BDR-NET > Nouveau dossier client : donnez un titre, choisissez le client et le conseiller, puis Publier.

Le client se connecte sur la page BDR-NET, voit ses dossiers, écrit à son conseiller et dépose ses documents.
Le conseiller répond depuis la même page (bouton « Répondre » sur la fiche du dossier dans l'administration).

== Mise à jour depuis « BDR Espace Client » ==

BDR-NET remplace l'ancienne extension « BDR Espace Client » et reprend automatiquement ses réglages,
dossiers, messages et documents.
1. Extensions : désactivez « BDR Espace Client ».
2. Installez et activez BDR-NET.
3. Supprimez « BDR Espace Client ». Les données sont conservées.
Si les deux sont actives en même temps, BDR-NET reste inactive et affiche un message pour éviter tout conflit.

== Désinstallation ==

Seuls les réglages sont supprimés. Les dossiers, messages, documents et comptes clients sont conservés.

== Changelog ==

= 1.2.0 =
* L'extension devient « BDR-NET » (nom, dossier, menu et réglages). Message de configuration limité aux écrans BDR-NET.

= 1.1.0 =
* Traductions anglaise et arabe, mise en page droite à gauche, dates localisées, e-mails dans la langue du destinataire.

= 1.0.0 =
* Première version.
