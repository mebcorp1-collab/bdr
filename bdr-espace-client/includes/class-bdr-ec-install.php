<?php
/**
 * Activation and upgrades: database tables, roles, capabilities and private storage folder.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Install {

	const DB_VERSION        = '1';
	const DB_VERSION_OPTION = 'bdr_ec_db_version';

	public static function activate() {
		self::install();
		BDR_EC_Data::register_post_type();
		flush_rewrite_rules();
	}

	public static function maybe_upgrade() {
		if ( get_option( self::DB_VERSION_OPTION ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	private static function install() {
		self::create_tables();
		self::add_roles();
		BDR_EC_Storage::ensure_dir();
		if ( false === get_option( BDR_EC_Admin::OPTION ) ) {
			add_option( BDR_EC_Admin::OPTION, BDR_EC_Admin::defaults() );
		}
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	private static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset  = $wpdb->get_charset_collate();
		$messages = BDR_EC_Data::table( 'messages' );
		$docs     = BDR_EC_Data::table( 'documents' );

		dbDelta(
			"CREATE TABLE {$messages} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				dossier_id bigint(20) unsigned NOT NULL,
				author_id bigint(20) unsigned NOT NULL,
				body longtext NOT NULL,
				created_at datetime NOT NULL,
				read_at datetime NULL DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY dossier_id (dossier_id)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE {$docs} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				dossier_id bigint(20) unsigned NOT NULL,
				uploader_id bigint(20) unsigned NOT NULL,
				original_name varchar(255) NOT NULL,
				stored_name varchar(64) NOT NULL,
				mime varchar(100) NOT NULL,
				size bigint(20) unsigned NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY dossier_id (dossier_id)
			) {$charset};"
		);
	}

	/**
	 * Capabilities of the "bdr_dossier" post type, granted to bank staff only.
	 */
	public static function staff_caps() {
		return array(
			'bdr_ec_manage',
			'edit_bdr_dossiers',
			'edit_others_bdr_dossiers',
			'edit_published_bdr_dossiers',
			'edit_private_bdr_dossiers',
			'publish_bdr_dossiers',
			'read_private_bdr_dossiers',
			'delete_bdr_dossiers',
			'delete_others_bdr_dossiers',
			'delete_published_bdr_dossiers',
			'delete_private_bdr_dossiers',
		);
	}

	private static function add_roles() {
		if ( ! get_role( 'bdr_client' ) ) {
			add_role( 'bdr_client', __( 'Client BDR', 'bdr-espace-client' ), array( 'read' => true ) );
		}
		if ( ! get_role( 'bdr_conseiller' ) ) {
			add_role( 'bdr_conseiller', __( 'Conseiller BDR', 'bdr-espace-client' ), array( 'read' => true, 'list_users' => true ) );
		}
		foreach ( array( 'administrator', 'bdr_conseiller' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				foreach ( self::staff_caps() as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}
}
