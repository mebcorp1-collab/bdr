<?php
/**
 * Plugin Name:       BDR Banque en ligne
 * Plugin URI:        https://www.bdr-dz.com/fr/banque-en-ligne/
 * Description:       Page « Banque en ligne » : bouton d'accès au portail e-banking officiel, applications mobiles, conseils de sécurité, FAQ et demandes d'adhésion. Ne collecte jamais d'identifiants ni de mots de passe.
 * Version:           1.0.0
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

define( 'BDR_EB_VERSION', '1.0.0' );
define( 'BDR_EB_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDR_EB_URL', plugin_dir_url( __FILE__ ) );

require_once BDR_EB_DIR . 'includes/class-bdr-eb-settings.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-shortcodes.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-faq.php';
require_once BDR_EB_DIR . 'includes/class-bdr-eb-signup.php';

function bdr_eb_init() {
	load_plugin_textdomain( 'bdr-banque-en-ligne', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	BDR_EB_Settings::init();
	BDR_EB_Shortcodes::init();
	BDR_EB_FAQ::init();
	BDR_EB_Signup::init();
}
add_action( 'plugins_loaded', 'bdr_eb_init' );

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
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bdr_eb_activate' );

function bdr_eb_action_links( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=bdr-banque-en-ligne' ) ) . '">' . esc_html__( 'Réglages', 'bdr-banque-en-ligne' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bdr_eb_action_links' );
