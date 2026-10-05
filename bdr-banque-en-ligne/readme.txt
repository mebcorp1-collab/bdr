=== BDR Banque en ligne ===
Contributors: bdr
Tags: banque en ligne, e-banking, espace client, faq, adhésion
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Page « Banque en ligne » pour www.bdr-dz.com : accès au portail e-banking officiel, applications mobiles, conseils de sécurité, FAQ et demandes d'adhésion.

== Description ==

Cette extension NE gère PAS la connexion des clients et ne collecte jamais d'identifiants, de mots de passe,
de numéros de compte ou de carte. Le bouton d'accès redirige vers le portail e-banking officiel de la banque,
qui reste seul à recevoir les identifiants.

* Menu « Banque en ligne » dans l'administration avec :
  * Réglages : adresse du portail (https obligatoire), liens Google Play / App Store, assistance, conseils de sécurité.
  * FAQ : ajoutez vos questions / réponses (champ « Ordre » pour les trier).
  * Demandes d'adhésion : liste des demandes reçues, avec un statut (Nouvelle, En cours, Traitée, Refusée).
* Formulaire d'adhésion : type de client, nom / raison sociale, mobile algérien, e-mail, agence, message.
  Protection anti-spam (nonce, champ piège, 1 demande / 5 min par IP). Un message contenant un numéro long
  (compte, carte) est refusé.
* L'e-mail de notification ne contient pas les coordonnées du client : seulement l'agence et un lien vers la demande.

== Shortcodes ==

* `[bdr_banque_en_ligne]` : page complète (accès, applications, sécurité, FAQ, adhésion, assistance).
* `[bdr_eb_bouton texte="Accéder à mon espace client"]`
* `[bdr_eb_applications]`
* `[bdr_eb_securite]`
* `[bdr_eb_faq]`
* `[bdr_eb_adhesion]`
* `[bdr_eb_assistance]`

== Installation ==

1. Extensions > Ajouter > Téléverser une extension, choisissez `bdr-banque-en-ligne.zip`, puis Activer.
2. Banque en ligne > Réglages : renseignez l'adresse officielle du portail e-banking et les autres informations.
3. Banque en ligne > FAQ : ajoutez vos questions.
4. Modifiez la page /fr/banque-en-ligne/ et insérez `[bdr_banque_en_ligne]`
   (ou les shortcodes séparés si vous voulez garder votre mise en page).

Pour la fiabilité des e-mails, utilisez une extension SMTP (par ex. WP Mail SMTP).

== Changelog ==

= 1.0.0 =
* Première version.
