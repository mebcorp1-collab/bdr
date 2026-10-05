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

	const PAGE_CHECKED_OPTION = 'bdr_ec_page_checked';

	public static function activate() {
		self::install();
		BDR_EC_Data::register_post_type();
		self::ensure_page();
		flush_rewrite_rules();
	}

	/**
	 * Make sure the client space page exists and is selected in the settings:
	 * reuse a published page that already contains [bdr_espace_client], otherwise create « BDR-NET ».
	 * Runs on activation and once after an update (the activation hook does not run on updates).
	 */
	public static function ensure_page() {
		update_option( self::PAGE_CHECKED_OPTION, 1, false );

		$page_id = (int) BDR_EC_Admin::get( 'page_id' );
		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			return $page_id;
		}

		global $wpdb;
		$page_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE %s ORDER BY ID ASC LIMIT 1",
				'%' . $wpdb->esc_like( '[bdr_espace_client' ) . '%'
			)
		);

		if ( ! $page_id ) {
			$page_id = wp_insert_post(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_title'     => 'BDR-NET',
					'post_name'      => 'bdr-net',
					'post_content'   => '[bdr_espace_client]',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
			if ( ! $page_id || is_wp_error( $page_id ) ) {
				return 0;
			}
		}

		$options            = (array) get_option( BDR_EC_Admin::OPTION, array() );
		$options['page_id'] = (int) $page_id;
		update_option( BDR_EC_Admin::OPTION, $options );
		return (int) $page_id;
	}

	/**
	 * After an update by « Remplacer la version installée », create or select the page once.
	 */
	public static function maybe_ensure_page() {
		if ( ! get_option( self::PAGE_CHECKED_OPTION ) && current_user_can( 'manage_options' ) ) {
			self::ensure_page();
			flush_rewrite_rules();
		}
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
			add_role( 'bdr_client', __( 'Client BDR', 'bdr-banque-en-ligne' ), array( 'read' => true ) );
		}
		if ( ! get_role( 'bdr_conseiller' ) ) {
			add_role( 'bdr_conseiller', __( 'Conseiller BDR', 'bdr-banque-en-ligne' ), array( 'read' => true, 'list_users' => true ) );
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
