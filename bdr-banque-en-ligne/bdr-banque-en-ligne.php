<?php
/**
 * Plugin Name:       BDR-NET Banque en ligne
 * Plugin URI:        https://www.bdr-dz.com/fr/banque-en-ligne/
 * Description:       Connexion BDR-NET (identifiant, mot de passe et code de sécurité) et espace client : suivi des dossiers, dépôt de documents et messagerie sécurisée entre le client et son conseiller, FAQ, conseils de sécurité et demandes d'adhésion. Aucune opération bancaire.
 * Version:           3.0.2
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            BDR
 * Author URI:        https://www.bdr-dz.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bdr-banque-en-ligne
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDR_EB_VERSION', '3.0.2' );
define( 'BDR_EB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDR_EB_URL', plugin_dir_url( __FILE__ ) );

require_once BDR_EB_DIR . 'includes/class-bdr-eb-settings.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-shortcodes.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-faq.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-signup.php';

/**
 * The client space was first shipped as separate plugins ("BDR Espace Client", then "BDR-NET").
 * They share its code, so loading both would crash the site: while one of them is still active,
 * the client space part stays off here and an admin notice asks to remove the old plugin.
 */
function bdr_eb_legacy_client_space_plugin() {
	$active = (array) get_option( 'active_plugins', array() );
	foreach ( array( 'bdr-espace-client/bdr-espace-client.php', 'bdr-net/bdr-net.php' ) as $legacy ) {
		if ( in_array( $legacy, $active, true ) ) {
			return $legacy;
		}
	}
	return defined( 'BDR_EC_VERSION' ) ? 'bdr-net/bdr-net.php' : '';
}

define( 'BDR_EB_CLIENT_SPACE', '' === bdr_eb_legacy_client_space_plugin() );

if ( BDR_EB_CLIENT_SPACE ) {
	define( 'BDR_EC_VERSION', BDR_EB_VERSION );
	define( 'BDR_EC_FILE', __FILE__ );
	define( 'BDR_EC_DIR', BDR_EB_DIR );
	define( 'BDR_EC_URL', BDR_EB_URL );

	require_once BDR_EB_DIR . 'includes/class-bdr-ec-install.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-ec-data.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-ec-storage.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-ec-actions.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-ec-front.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-ec-admin.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-net-captcha.php';
	require_once BDR_EB_DIR . 'includes/class-bdr-net-login.php';
}

function bdr_eb_init() {
	load_plugin_textdomain( 'bdr-banque-en-ligne', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	BDR_EB_Settings::init();
	BDR_EB_Shortcodes::init();
	BDR_EB_FAQ::init();
	BDR_EB_Signup::init();

	if ( BDR_EB_CLIENT_SPACE ) {
		BDR_EC_Install::maybe_upgrade();
		BDR_EC_Data::init();
		BDR_EC_Actions::init();
		BDR_EC_Front::init();
		BDR_EC_Admin::init();
		BDR_NET_Login::init();
	} else {
		add_action( 'admin_notices', 'bdr_eb_legacy_notice' );
	}
}
add_action( 'plugins_loaded', 'bdr_eb_init' );

function bdr_eb_legacy_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'BDR Banque en ligne contient désormais l\'espace client. Désactivez puis supprimez l\'ancienne extension « BDR Espace Client » ou « BDR-NET » : ses dossiers, messages et documents sont repris automatiquement.', 'bdr-banque-en-ligne' ) . '</p></div>';
}

function bdr_eb_register_assets() {
	wp_register_style( 'bdr-banque-en-ligne', BDR_EB_URL . 'assets/css/bdr-banque-en-ligne.css', array(), BDR_EB_VERSION );
}
add_action( 'wp_enqueue_scripts', 'bdr_eb_register_assets', 5 );

function bdr_eb_activate() {
	if ( false === get_option( BDR_EB_Settings::OPTION ) ) {
		add_option( BDR_EB_Settings::OPTION, BDR_EB_Settings::defaults() );
	}
	BDR_EB_FAQ::register_post_type();
	BDR_EB_Signup::register_post_type();
	if ( BDR_EB_CLIENT_SPACE ) {
		BDR_EC_Install::activate();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bdr_eb_activate' );

function bdr_eb_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=bdr-banque-en-ligne' ) ) . '">' . esc_html__( 'Réglages', 'bdr-banque-en-ligne' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bdr_eb_action_links' );
