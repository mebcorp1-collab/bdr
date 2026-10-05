<?php
/**
 * Form handlers (admin-post.php), secure downloads, notifications and client account restrictions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Actions {

	public static function init() {
		add_action( 'admin_post_bdr_ec_message', array( __CLASS__, 'send_message' ) );
		add_action( 'admin_post_bdr_ec_upload', array( __CLASS__, 'upload' ) );
		add_action( 'admin_post_bdr_ec_download', array( __CLASS__, 'download' ) );
		add_action( 'admin_post_nopriv_bdr_ec_download', array( __CLASS__, 'download' ) );

		// Clients only use the front-end space: no dashboard, no admin bar.
		add_action( 'admin_init', array( __CLASS__, 'keep_clients_out_of_admin' ) );
		add_filter( 'show_admin_bar', array( __CLASS__, 'hide_admin_bar' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );

		// Brute-force protection on every WordPress login.
		add_filter( 'authenticate', array( __CLASS__, 'block_locked_login' ), 30, 2 );
		add_action( 'wp_login_failed', array( __CLASS__, 'record_failed_login' ) );
		add_action( 'wp_login', array( __CLASS__, 'clear_failed_logins' ), 10, 2 );
	}

	const MAX_USER_FAILURES = 5;  // Per username: stops guessing one client's password.
	const MAX_IP_FAILURES   = 20; // Per IP: higher, as a whole branch or company may share one IP.
	const LOCK_MINUTES      = 15;

	private static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	/**
	 * Failures are counted per IP and per username, so neither a single attacker
	 * nor a distributed attack on one account can keep guessing.
	 */
	private static function lock_keys( $username ) {
		return array(
			'bdr_ec_fail_ip_' . md5( self::client_ip() )                         => self::MAX_IP_FAILURES,
			'bdr_ec_fail_user_' . md5( strtolower( trim( (string) $username ) ) ) => self::MAX_USER_FAILURES,
		);
	}

	public static function block_locked_login( $user, $username ) {
		if ( '' === trim( (string) $username ) ) {
			return $user;
		}
		foreach ( self::lock_keys( $username ) as $key => $max ) {
			if ( (int) get_transient( $key ) >= $max ) {
				return new WP_Error(
					'bdr_ec_locked',
					sprintf(
						/* translators: %d: minutes */
						__( 'Trop de tentatives de connexion. Pour votre sécurité, réessayez dans %d minutes ou contactez votre agence.', 'bdr-net' ),
						self::LOCK_MINUTES
					)
				);
			}
		}
		return $user;
	}

	public static function record_failed_login( $username ) {
		foreach ( array_keys( self::lock_keys( $username ) ) as $key ) {
			set_transient( $key, (int) get_transient( $key ) + 1, self::LOCK_MINUTES * MINUTE_IN_SECONDS );
		}
	}

	public static function clear_failed_logins( $username, $user ) {
		$keys = array_keys( self::lock_keys( $username ) );
		delete_transient( $keys[1] );
	}

	public static function is_client_only( $user = null ) {
		$user = $user ? $user : wp_get_current_user();
		return $user && $user->exists() && in_array( 'bdr_client', (array) $user->roles, true ) && ! user_can( $user, 'bdr_ec_manage' );
	}

	public static function keep_clients_out_of_admin() {
		if ( wp_doing_ajax() || 'admin-post.php' === basename( isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '' ) ) {
			return;
		}
		if ( self::is_client_only() ) {
			wp_safe_redirect( BDR_EC_Front::space_url() );
			exit;
		}
	}

	public static function hide_admin_bar( $show ) {
		return self::is_client_only() ? false : $show;
	}

	public static function login_redirect( $redirect_to, $requested, $user ) {
		if ( $user instanceof WP_User && self::is_client_only( $user ) ) {
			return BDR_EC_Front::space_url();
		}
		return $redirect_to;
	}

	/**
	 * Simple per-user rate limit: $max actions per 10 minutes.
	 */
	private static function rate_limited( $action, $max ) {
		$key   = 'bdr_ec_rl_' . $action . '_' . get_current_user_id();
		$count = (int) get_transient( $key );
		if ( $count >= $max ) {
			return true;
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}

	/**
	 * Common checks for a POST on a dossier. Returns the dossier ID, or redirects with an error.
	 */
	private static function checked_dossier( $nonce_action ) {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( BDR_EC_Front::space_url() );
			exit;
		}
		$dossier_id = isset( $_POST['dossier'] ) ? absint( $_POST['dossier'] ) : 0;

		if ( ! isset( $_POST['_bdr_ec_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_bdr_ec_nonce'] ) ), $nonce_action . '_' . $dossier_id ) ) {
			self::back( $dossier_id, 'expired' );
		}
		if ( ! BDR_EC_Data::get_dossier( $dossier_id ) ) {
			self::back( 0, 'denied' );
		}
		if ( ! BDR_EC_Data::can_write( $dossier_id ) ) {
			self::back( $dossier_id, 'closed' );
		}
		return $dossier_id;
	}

	private static function back( $dossier_id, $notice ) {
		wp_safe_redirect( BDR_EC_Front::space_url( $dossier_id, array( 'bdr_ec_notice' => $notice ) ) );
		exit;
	}

	public static function send_message() {
		$dossier_id = self::checked_dossier( 'bdr_ec_message' );

		$body = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$body = trim( $body );
		if ( '' === $body ) {
			self::back( $dossier_id, 'empty' );
		}
		if ( mb_strlen( $body ) > 5000 ) {
			self::back( $dossier_id, 'too_long' );
		}
		if ( self::rate_limited( 'msg', 20 ) ) {
			self::back( $dossier_id, 'rate' );
		}

		if ( ! BDR_EC_Data::add_message( $dossier_id, get_current_user_id(), $body ) ) {
			self::back( $dossier_id, 'error' );
		}
		self::notify( $dossier_id, 'message' );
		self::back( $dossier_id, 'sent' );
	}

	public static function upload() {
		$dossier_id = self::checked_dossier( 'bdr_ec_upload' );

		if ( self::rate_limited( 'upload', 15 ) ) {
			self::back( $dossier_id, 'rate' );
		}

		$file   = isset( $_FILES['document'] ) ? $_FILES['document'] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- validated in store_upload().
		$stored = BDR_EC_Storage::store_upload( $file );
		if ( is_wp_error( $stored ) ) {
			$codes = array(
				'bdr_ec_too_big' => 'too_big',
				'bdr_ec_type'    => 'bad_type',
			);
			self::back( $dossier_id, isset( $codes[ $stored->get_error_code() ] ) ? $codes[ $stored->get_error_code() ] : 'upload_error' );
		}

		$id = BDR_EC_Data::add_document( $dossier_id, get_current_user_id(), $stored['original'], $stored['stored'], $stored['mime'], $stored['size'] );
		if ( ! $id ) {
			wp_delete_file( BDR_EC_Storage::path( $stored['stored'] ) );
			self::back( $dossier_id, 'error' );
		}
		self::notify( $dossier_id, 'document' );
		self::back( $dossier_id, 'uploaded' );
	}

	public static function download_url( $doc_id ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=bdr_ec_download&doc=' . (int) $doc_id ), 'bdr_ec_download_' . (int) $doc_id );
	}

	/**
	 * Stream a document after checking the nonce, the login and the dossier access.
	 */
	public static function download() {
		$doc_id = isset( $_GET['doc'] ) ? absint( $_GET['doc'] ) : 0;

		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'bdr_ec_download_' . $doc_id ) ) {
			wp_die( esc_html__( 'Lien expiré. Retournez dans votre espace client et réessayez.', 'bdr-net' ), '', array( 'response' => 403 ) );
		}

		$doc = BDR_EC_Data::get_document( $doc_id );
		if ( ! $doc || ! BDR_EC_Data::get_dossier( $doc->dossier_id ) ) {
			wp_die( esc_html__( 'Document introuvable.', 'bdr-net' ), '', array( 'response' => 404 ) );
		}
		$path = BDR_EC_Storage::path( $doc->stored_name );
		if ( '' === $path || ! is_readable( $path ) ) {
			wp_die( esc_html__( 'Document introuvable.', 'bdr-net' ), '', array( 'response' => 404 ) );
		}

		$name  = sanitize_file_name( $doc->original_name );
		$ascii = preg_replace( '/[^A-Za-z0-9._-]/', '_', $name );

		nocache_headers();
		header( 'Content-Type: ' . $doc->mime );
		header( 'Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: private, no-store, max-age=0' );
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}

	/**
	 * E-mail the other side, in the recipient's language (profile setting).
	 * The e-mail never contains the message or the document: only a link to log in.
	 */
	private static function notify( $dossier_id, $what ) {
		$dossier = get_post( $dossier_id );
		$client  = BDR_EC_Data::client_id( $dossier_id );
		$to_bank = get_current_user_id() === $client;

		$recipient = $to_bank ? get_userdata( BDR_EC_Data::advisor_id( $dossier_id ) ) : get_userdata( $client );
		if ( $recipient ) {
			$to = $recipient->user_email;
		} elseif ( $to_bank ) {
			$to = BDR_EC_Admin::get( 'notify_email' );
			$to = is_email( $to ) ? $to : get_option( 'admin_email' );
		} else {
			return;
		}

		$switched = $recipient ? switch_to_locale( get_user_locale( $recipient ) ) : false;

		if ( $to_bank ) {
			$subject = 'document' === $what
				/* translators: %s: dossier title */
				? sprintf( __( '[Espace client] Nouveau document — %s', 'bdr-net' ), $dossier->post_title )
				/* translators: %s: dossier title */
				: sprintf( __( '[Espace client] Nouveau message — %s', 'bdr-net' ), $dossier->post_title );
			$body    = sprintf(
				/* translators: 1: client name, 2: dossier title, 3: link */
				__( "%1\$s a ajouté un élément au dossier « %2\$s ».\n\nConsulter le dossier : %3\$s", 'bdr-net' ),
				get_userdata( $client ) ? get_userdata( $client )->display_name : '',
				$dossier->post_title,
				BDR_EC_Front::space_url( $dossier_id )
			);
		} else {
			/* translators: %s: name of the client space, e.g. BDR-NET */
			$subject = sprintf( __( '%s — Nouvelle notification dans votre espace client', 'bdr-net' ), BDR_EC_Admin::get( 'space_name' ) );
			$body    = sprintf(
				/* translators: 1: client name, 2: dossier title, 3: link */
				__( "Bonjour %1\$s,\n\nVotre conseiller a ajouté un nouvel élément à votre dossier « %2\$s ».\n\nPour le consulter, connectez-vous à votre espace client : %3\$s\n\nPour votre sécurité, ce message ne contient aucune information personnelle. La BDR ne vous demandera jamais vos mots de passe ou codes par e-mail.", 'bdr-net' ),
				$recipient->display_name,
				$dossier->post_title,
				BDR_EC_Front::space_url()
			);
		}

		if ( $switched ) {
			restore_previous_locale();
		}
		wp_mail( $to, $subject, $body );
	}
}
