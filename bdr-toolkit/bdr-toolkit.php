<?php
/**
 * Plugin Name:       BDR Toolkit
 * Plugin URI:        https://www.bdr-dz.com
 * Description:       Outils pour le site BDR : coordonnées centralisées, bouton WhatsApp / appel flottant, formulaire de contact avec historique des messages.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            BDR
 * Author URI:        https://www.bdr-dz.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bdr-toolkit
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDR_TOOLKIT_VERSION', '1.0.0' );
define( 'BDR_TOOLKIT_FILE', __FILE__ );
define( 'BDR_TOOLKIT_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDR_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );

require_once BDR_TOOLKIT_DIR . 'includes/class-bdr-settings.php';
require_once BDR_TOOLKIT_DIR . 'includes/class-bdr-shortcodes.php';
require_once BDR_TOOLKIT_DIR . 'includes/class-bdr-floating-button.php';
require_once BDR_TOOLKIT_DIR . 'includes/class-bdr-contact-form.php';

/**
 * Boot the plugin.
 */
function bdr_toolkit_init() {
	load_plugin_textdomain( 'bdr-toolkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	BDR_Settings::init();
	BDR_Shortcodes::init();
	BDR_Floating_Button::init();
	BDR_Contact_Form::init();
}
add_action( 'plugins_loaded', 'bdr_toolkit_init' );

/**
 * Register front-end assets (enqueued only when needed).
 */
function bdr_toolkit_register_assets() {
	wp_register_style( 'bdr-toolkit', BDR_TOOLKIT_URL . 'assets/css/bdr-toolkit.css', array(), BDR_TOOLKIT_VERSION );
}
add_action( 'wp_enqueue_scripts', 'bdr_toolkit_register_assets', 5 );

/**
 * Activation: set default options and register the message post type for rewrite rules.
 */
function bdr_toolkit_activate() {
	if ( false === get_option( BDR_Settings::OPTION ) ) {
		add_option( BDR_Settings::OPTION, BDR_Settings::defaults() );
	}
	BDR_Contact_Form::register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bdr_toolkit_activate' );

/**
 * Settings link on the Plugins screen.
 */
function bdr_toolkit_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=bdr-toolkit' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Réglages', 'bdr-toolkit' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bdr_toolkit_action_links' );
