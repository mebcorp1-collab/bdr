<?php
/**
 * Plugin Name:       BDR Espace Client
 * Plugin URI:        https://www.bdr-dz.com
 * Description:       Espace client privé : suivi des dossiers, dépôt de documents et messagerie sécurisée entre le client et son conseiller. Aucune opération bancaire.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            BDR
 * Author URI:        https://www.bdr-dz.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bdr-espace-client
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BDR_EC_VERSION', '1.0.0' );
define( 'BDR_EC_FILE', __FILE__ );
define( 'BDR_EC_DIR', plugin_dir_path( __FILE__ ) );
define( 'BDR_EC_URL', plugin_dir_url( __FILE__ ) );

require_once BDR_EC_DIR . 'includes/class-bdr-ec-install.php';
require_once BDR_EC_DIR . 'includes/class-bdr-ec-data.php';
require_once BDR_EC_DIR . 'includes/class-bdr-ec-storage.php';
require_once BDR_EC_DIR . 'includes/class-bdr-ec-actions.php';
require_once BDR_EC_DIR . 'includes/class-bdr-ec-front.php';
require_once BDR_EC_DIR . 'includes/class-bdr-ec-admin.php';

function bdr_ec_init() {
	load_plugin_textdomain( 'bdr-espace-client', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	BDR_EC_Install::maybe_upgrade();
	BDR_EC_Data::init();
	BDR_EC_Actions::init();
	BDR_EC_Front::init();
	BDR_EC_Admin::init();
}
add_action( 'plugins_loaded', 'bdr_ec_init' );

register_activation_hook( __FILE__, array( 'BDR_EC_Install', 'activate' ) );
