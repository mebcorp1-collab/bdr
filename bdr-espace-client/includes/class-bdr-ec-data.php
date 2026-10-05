<?php
/**
 * Dossiers (custom post type), messages and documents (custom tables), and every access rule.
 *
 * Access rule: a dossier is visible to its client (post meta _bdr_ec_client) and to bank staff
 * (capability bdr_ec_manage). Nobody else, whatever the URL or ID they try.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Data {

	const POST_TYPE = 'bdr_dossier';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Dossiers clients', 'bdr-espace-client' ),
					'singular_name' => __( 'Dossier client', 'bdr-espace-client' ),
					'menu_name'     => __( 'Espace client', 'bdr-espace-client' ),
					'all_items'     => __( 'Dossiers', 'bdr-espace-client' ),
					'add_new'       => __( 'Nouveau dossier', 'bdr-espace-client' ),
					'add_new_item'  => __( 'Nouveau dossier client', 'bdr-espace-client' ),
					'edit_item'     => __( 'Dossier client', 'bdr-espace-client' ),
					'search_items'  => __( 'Rechercher un dossier', 'bdr-espace-client' ),
					'not_found'     => __( 'Aucun dossier.', 'bdr-espace-client' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'menu_icon'       => 'dashicons-portfolio',
				'menu_position'   => 26,
				'supports'        => array( 'title' ),
				'capability_type' => array( 'bdr_dossier', 'bdr_dossiers' ),
				'map_meta_cap'    => true,
			)
		);
	}

	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'bdr_ec_' . $name;
	}

	public static function statuses() {
		return array(
			'ouvert'       => __( 'Ouvert', 'bdr-espace-client' ),
			'en_cours'     => __( 'En cours de traitement', 'bdr-espace-client' ),
			'attente_docs' => __( 'En attente de documents', 'bdr-espace-client' ),
			'cloture'      => __( 'Clôturé', 'bdr-espace-client' ),
		);
	}

	public static function status( $dossier_id ) {
		$status = get_post_meta( $dossier_id, '_bdr_ec_status', true );
		return array_key_exists( $status, self::statuses() ) ? $status : 'ouvert';
	}

	public static function status_label( $dossier_id ) {
		$statuses = self::statuses();
		return $statuses[ self::status( $dossier_id ) ];
	}

	/* ---------------------------------------------------------------------
	 * Access control
	 * ------------------------------------------------------------------- */

	public static function is_staff( $user_id = 0 ) {
		$user_id = $user_id ? $user_id : get_current_user_id();
		return $user_id && user_can( $user_id, 'bdr_ec_manage' );
	}

	public static function client_id( $dossier_id ) {
		return (int) get_post_meta( $dossier_id, '_bdr_ec_client', true );
	}

	public static function advisor_id( $dossier_id ) {
		return (int) get_post_meta( $dossier_id, '_bdr_ec_conseiller', true );
	}

	/**
	 * Return the dossier post when $user_id may see it, null otherwise.
	 */
	public static function get_dossier( $dossier_id, $user_id = 0 ) {
		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return null;
		}
		$post = get_post( (int) $dossier_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return null;
		}
		if ( self::is_staff( $user_id ) || self::client_id( $post->ID ) === (int) $user_id ) {
			return $post;
		}
		return null;
	}

	/**
	 * Clients cannot write in a closed dossier; staff always can.
	 */
	public static function can_write( $dossier_id, $user_id = 0 ) {
		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! self::get_dossier( $dossier_id, $user_id ) ) {
			return false;
		}
		return self::is_staff( $user_id ) || 'cloture' !== self::status( $dossier_id );
	}

	/**
	 * Dossiers listed for a user: their own for a client, all for staff.
	 */
	public static function dossiers_for( $user_id = 0 ) {
		$user_id = $user_id ? $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		$args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);
		if ( ! self::is_staff( $user_id ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_bdr_ec_client',
					'value' => (int) $user_id,
				),
			);
		}
		return get_posts( $args );
	}

	/* ---------------------------------------------------------------------
	 * Messages
	 * ------------------------------------------------------------------- */

	public static function messages( $dossier_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table( 'messages' ) . ' WHERE dossier_id = %d ORDER BY created_at ASC, id ASC', $dossier_id )
		);
	}

	public static function add_message( $dossier_id, $author_id, $body ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table( 'messages' ),
			array(
				'dossier_id' => (int) $dossier_id,
				'author_id'  => (int) $author_id,
				'body'       => $body,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%s' )
		);
		if ( $ok ) {
			self::touch( $dossier_id );
		}
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Messages written by the other side that $user_id has not read yet.
	 * For staff, "the other side" is the client; for the client, it is the bank.
	 */
	public static function unread_count( $dossier_id, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? $user_id : get_current_user_id();
		$client  = self::client_id( $dossier_id );
		$table   = self::table( 'messages' );

		if ( self::is_staff( $user_id ) ) {
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE dossier_id = %d AND author_id = %d AND read_at IS NULL", $dossier_id, $client );
		} else {
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE dossier_id = %d AND author_id <> %d AND read_at IS NULL", $dossier_id, $client );
		}
		return (int) $wpdb->get_var( $sql );
	}

	public static function mark_read( $dossier_id, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? $user_id : get_current_user_id();
		$client  = self::client_id( $dossier_id );
		$table   = self::table( 'messages' );
		$now     = current_time( 'mysql', true );

		if ( self::is_staff( $user_id ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET read_at = %s WHERE dossier_id = %d AND author_id = %d AND read_at IS NULL", $now, $dossier_id, $client ) );
		} elseif ( $client === (int) $user_id ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET read_at = %s WHERE dossier_id = %d AND author_id <> %d AND read_at IS NULL", $now, $dossier_id, $client ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Documents
	 * ------------------------------------------------------------------- */

	public static function documents( $dossier_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::table( 'documents' ) . ' WHERE dossier_id = %d ORDER BY created_at DESC, id DESC', $dossier_id )
		);
	}

	public static function get_document( $doc_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table( 'documents' ) . ' WHERE id = %d', $doc_id ) );
	}

	public static function add_document( $dossier_id, $uploader_id, $original_name, $stored_name, $mime, $size ) {
		global $wpdb;
		$ok = $wpdb->insert(
			self::table( 'documents' ),
			array(
				'dossier_id'    => (int) $dossier_id,
				'uploader_id'   => (int) $uploader_id,
				'original_name' => $original_name,
				'stored_name'   => $stored_name,
				'mime'          => $mime,
				'size'          => (int) $size,
				'created_at'    => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%d', '%s' )
		);
		if ( $ok ) {
			self::touch( $dossier_id );
		}
		return $ok ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Bump the dossier's modified date so the most active dossiers are listed first.
	 */
	private static function touch( $dossier_id ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_modified'     => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', true ),
			),
			array( 'ID' => (int) $dossier_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		clean_post_cache( (int) $dossier_id );
	}

	/**
	 * Display name for a message author: the client's name, or "Votre conseiller BDR" for staff when viewed by the client.
	 */
	public static function author_label( $author_id, $dossier_id, $viewer_id = 0 ) {
		$viewer_id = $viewer_id ? $viewer_id : get_current_user_id();
		$is_client = self::client_id( $dossier_id ) === (int) $author_id;

		if ( ! $is_client && ! self::is_staff( $viewer_id ) ) {
			return __( 'Votre conseiller BDR', 'bdr-espace-client' );
		}
		$user = get_userdata( $author_id );
		$name = $user ? $user->display_name : __( 'Utilisateur supprimé', 'bdr-espace-client' );
		return $is_client ? $name : $name . ' (BDR)';
	}
}
