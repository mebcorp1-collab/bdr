=== BDR Toolkit ===
Contributors: bdr
Tags: contact, whatsapp, formulaire, algérie
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Outils pour le site www.bdr-dz.com : coordonnées, bouton WhatsApp / appel flottant et formulaire de contact.

== Description ==

* Page de réglages (Réglages > BDR Toolkit) pour saisir une seule fois les coordonnées de l'entreprise.
* Bouton flottant WhatsApp et/ou appel sur toutes les pages (position et couleur réglables).
* Les numéros algériens au format local (0555 12 34 56) sont convertis automatiquement en +213.
* Formulaire de contact avec protection anti-spam (nonce, champ piège, 1 envoi par minute et par IP).
* Chaque message est envoyé par e-mail et enregistré dans le menu « Messages BDR ».

== Shortcodes ==

* `[bdr_contact_form]` : formulaire de contact. Option : `button="Envoyer"`.
* `[bdr_contact_info]` : bloc complet des coordonnées + réseaux sociaux.
* `[bdr_info field="phone"]` : une seule donnée (company_name, phone, whatsapp, email, address, hours). Option : `link="no"`.
* `[bdr_social]` : liens vers Facebook, Instagram et LinkedIn.
* `[bdr_whatsapp text="Écrivez-nous"]` : bouton WhatsApp dans le contenu.

== Installation ==

1. Compressez le dossier `bdr-toolkit` en `bdr-toolkit.zip`.
2. Dans WordPress : Extensions > Ajouter > Téléverser une extension, choisissez le zip puis Activer.
3. Allez dans Réglages > BDR Toolkit et renseignez vos coordonnées.
4. Ajoutez `[bdr_contact_form]` dans votre page Contact.

Pour que les e-mails arrivent bien, il est conseillé d'utiliser une extension SMTP (par ex. WP Mail SMTP).

== Changelog ==

= 1.0.0 =
* Première version.
