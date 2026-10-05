<?php
/**
 * Front-end client space: [bdr_espace_client].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Front {

	public static function init() {
		add_shortcode( 'bdr_espace_client', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'template_redirect', array( __CLASS__, 'no_cache' ) );
	}

	public static function register_assets() {
		wp_register_style( 'bdr-espace-client', BDR_EC_URL . 'assets/css/bdr-espace-client.css', array(), BDR_EC_VERSION );
	}

	/**
	 * Private pages must never be cached by the browser, a proxy or a cache plugin.
	 */
	public static function no_cache() {
		$page_id = (int) BDR_EC_Admin::get( 'page_id' );
		if ( $page_id && is_page( $page_id ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}
	}

	public static function space_url( $dossier_id = 0, $args = array() ) {
		$page_id = (int) BDR_EC_Admin::get( 'page_id' );
		$url     = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		if ( $dossier_id ) {
			$args['dossier'] = (int) $dossier_id;
		}
		return $args ? add_query_arg( $args, $url ) : $url;
	}

	private static function notices() {
		return array(
			'sent'         => array( 'success', __( 'Votre message a été envoyé.', 'bdr-banque-en-ligne' ) ),
			'uploaded'     => array( 'success', __( 'Votre document a été déposé.', 'bdr-banque-en-ligne' ) ),
			'expired'      => array( 'error', __( 'La session a expiré. Veuillez réessayer.', 'bdr-banque-en-ligne' ) ),
			'denied'       => array( 'error', __( 'Ce dossier n\'est pas accessible.', 'bdr-banque-en-ligne' ) ),
			'closed'       => array( 'error', __( 'Ce dossier est clôturé : vous ne pouvez plus y ajouter de message ou de document.', 'bdr-banque-en-ligne' ) ),
			'empty'        => array( 'error', __( 'Le message est vide.', 'bdr-banque-en-ligne' ) ),
			'too_long'     => array( 'error', __( 'Le message est trop long (5 000 caractères maximum).', 'bdr-banque-en-ligne' ) ),
			'rate'         => array( 'error', __( 'Trop d\'envois en peu de temps. Veuillez patienter quelques minutes.', 'bdr-banque-en-ligne' ) ),
			'too_big'      => array( 'error', __( 'Le fichier est trop volumineux.', 'bdr-banque-en-ligne' ) ),
			'bad_type'     => array( 'error', __( 'Type de fichier non autorisé. Formats acceptés : PDF, JPG, PNG.', 'bdr-banque-en-ligne' ) ),
			'upload_error' => array( 'error', __( 'Le fichier n\'a pas pu être déposé. Veuillez réessayer.', 'bdr-banque-en-ligne' ) ),
			'error'        => array( 'error', __( 'Une erreur est survenue. Veuillez réessayer.', 'bdr-banque-en-ligne' ) ),
		);
	}

	private static function notice_html() {
		$code    = isset( $_GET['bdr_ec_notice'] ) ? sanitize_key( wp_unslash( $_GET['bdr_ec_notice'] ) ) : '';
		$notices = self::notices();
		if ( ! isset( $notices[ $code ] ) ) {
			return '';
		}
		list( $type, $text ) = $notices[ $code ];
		return '<div class="bdr-ec-notice bdr-ec-notice--' . esc_attr( $type ) . '" role="' . ( 'error' === $type ? 'alert' : 'status' ) . '">' . esc_html( $text ) . '</div>';
	}

	public static function render() {
		wp_enqueue_style( 'bdr-espace-client' );

		if ( ! is_user_logged_in() ) {
			return '<div class="bdr-ec">' . self::login() . '</div>';
		}

		$user = wp_get_current_user();
		if ( ! BDR_EC_Data::is_staff() && ! in_array( 'bdr_client', (array) $user->roles, true ) ) {
			return '<div class="bdr-ec"><div class="bdr-ec-notice bdr-ec-notice--error">' . esc_html__( 'Votre compte n\'a pas accès à l\'espace client.', 'bdr-banque-en-ligne' ) . '</div></div>';
		}

		$dossier_id = isset( $_GET['dossier'] ) ? absint( $_GET['dossier'] ) : 0;
		$html       = '<div class="bdr-ec">' . self::topbar( $user ) . self::notice_html();

		if ( $dossier_id ) {
			$dossier = BDR_EC_Data::get_dossier( $dossier_id );
			$html   .= $dossier ? self::dossier( $dossier ) : '<div class="bdr-ec-notice bdr-ec-notice--error">' . esc_html__( 'Ce dossier n\'est pas accessible.', 'bdr-banque-en-ligne' ) . '</div>';
		} else {
			$html .= self::dossier_list();
		}
		return $html . '</div>';
	}

	private static function login() {
		$name  = BDR_EC_Admin::get( 'space_name' );
		$html  = '<div class="bdr-ec-login">';
		/* translators: %s: name of the client space, e.g. BDR-NET */
		$html .= '<h2>' . esc_html( sprintf( __( 'Connexion à %s', 'bdr-banque-en-ligne' ), $name ) ) . '</h2>';
		$html .= '<p>' . esc_html__( 'Suivez vos dossiers, déposez vos documents et échangez avec votre conseiller en toute confidentialité.', 'bdr-banque-en-ligne' ) . '</p>';
		$html .= '<div class="bdr-ec-warning"><strong>' . esc_html__( 'Votre sécurité :', 'bdr-banque-en-ligne' ) . '</strong> '
			. esc_html__( 'connectez-vous uniquement depuis le site officiel de la BDR (vérifiez l\'adresse et le cadenas). La BDR ne vous demandera jamais votre mot de passe par e-mail, SMS ou téléphone. Ne communiquez jamais vos codes de carte.', 'bdr-banque-en-ligne' ) . '</div>';
		$html .= wp_login_form(
			array(
				'echo'           => false,
				'redirect'       => self::space_url(),
				'label_username' => __( 'Identifiant ou e-mail', 'bdr-banque-en-ligne' ),
				'label_password' => __( 'Mot de passe', 'bdr-banque-en-ligne' ),
				'label_remember' => __( 'Rester connecté', 'bdr-banque-en-ligne' ),
				'label_log_in'   => __( 'Se connecter', 'bdr-banque-en-ligne' ),
				'remember'       => false,
			)
		);
		$html .= '<p class="bdr-ec-small"><a href="' . esc_url( wp_lostpassword_url( self::space_url() ) ) . '">' . esc_html__( 'Mot de passe oublié ?', 'bdr-banque-en-ligne' ) . '</a> · '
			. esc_html__( 'Pas encore d\'accès ? Demandez-le à votre agence.', 'bdr-banque-en-ligne' ) . '</p>';
		return $html . '</div>';
	}

	private static function topbar( $user ) {
		return '<div class="bdr-ec-topbar"><span>' . sprintf(
			/* translators: %s: user display name */
			esc_html__( 'Bonjour %s', 'bdr-banque-en-ligne' ),
			'<strong>' . esc_html( $user->display_name ) . '</strong>'
		) . '</span><a href="' . esc_url( wp_logout_url( self::space_url() ) ) . '">' . esc_html__( 'Se déconnecter', 'bdr-banque-en-ligne' ) . '</a></div>';
	}

	private static function dossier_list() {
		$dossiers = BDR_EC_Data::dossiers_for();
		$html     = '<h2>' . esc_html__( 'Mes dossiers', 'bdr-banque-en-ligne' ) . '</h2>';

		if ( ! $dossiers ) {
			return $html . '<p>' . esc_html__( 'Vous n\'avez aucun dossier pour le moment. Votre conseiller en ouvrira un si nécessaire.', 'bdr-banque-en-ligne' ) . '</p>';
		}

		$staff = BDR_EC_Data::is_staff();
		$html .= '<table class="bdr-ec-table"><thead><tr><th>' . esc_html__( 'Dossier', 'bdr-banque-en-ligne' ) . '</th>';
		if ( $staff ) {
			$html .= '<th>' . esc_html__( 'Client', 'bdr-banque-en-ligne' ) . '</th>';
		}
		$html .= '<th>' . esc_html__( 'Statut', 'bdr-banque-en-ligne' ) . '</th><th>' . esc_html__( 'Dernière activité', 'bdr-banque-en-ligne' ) . '</th></tr></thead><tbody>';

		foreach ( $dossiers as $dossier ) {
			$unread = BDR_EC_Data::unread_count( $dossier->ID );
			$html  .= '<tr><td><a dir="auto" href="' . esc_url( self::space_url( $dossier->ID ) ) . '">' . esc_html( get_the_title( $dossier ) ) . '</a>';
			if ( $unread ) {
				/* translators: %d: number of unread messages */
				$html .= ' <span class="bdr-ec-badge">' . esc_html( sprintf( _n( '%d nouveau', '%d nouveaux', $unread, 'bdr-banque-en-ligne' ), $unread ) ) . '</span>';
			}
			$html .= '</td>';
			if ( $staff ) {
				$client = get_userdata( BDR_EC_Data::client_id( $dossier->ID ) );
				$html  .= '<td>' . esc_html( $client ? $client->display_name : '—' ) . '</td>';
			}
			$html .= '<td><span class="bdr-ec-status bdr-ec-status--' . esc_attr( BDR_EC_Data::status( $dossier->ID ) ) . '">' . esc_html( BDR_EC_Data::status_label( $dossier->ID ) ) . '</span></td>';
			$html .= '<td>' . self::format_date( $dossier->post_modified_gmt ) . '</td></tr>';
		}
		return $html . '</tbody></table>';
	}

	/**
	 * Localized date (month names follow the current language) from a GMT MySQL datetime.
	 */
	public static function format_date( $gmt ) {
		return esc_html( wp_date( get_option( 'date_format' ) . ' H:i', strtotime( $gmt . ' UTC' ) ) );
	}

	private static function dossier( $dossier ) {
		$id       = $dossier->ID;
		$writable = BDR_EC_Data::can_write( $id );
		$messages = BDR_EC_Data::messages( $id );
		$docs     = BDR_EC_Data::documents( $id );
		$me       = get_current_user_id();

		BDR_EC_Data::mark_read( $id );

		$html  = '<p><a href="' . esc_url( self::space_url() ) . '">' . ( is_rtl() ? '&rarr; ' : '&larr; ' ) . esc_html__( 'Mes dossiers', 'bdr-banque-en-ligne' ) . '</a></p>';
		$html .= '<div class="bdr-ec-head"><h2 dir="auto">' . esc_html( get_the_title( $dossier ) ) . '</h2>'
			. '<span class="bdr-ec-status bdr-ec-status--' . esc_attr( BDR_EC_Data::status( $id ) ) . '">' . esc_html( BDR_EC_Data::status_label( $id ) ) . '</span></div>';

		// Messages.
		$html .= '<section class="bdr-ec-section"><h3>' . esc_html__( 'Messages', 'bdr-banque-en-ligne' ) . '</h3><div class="bdr-ec-thread">';
		if ( ! $messages ) {
			$html .= '<p class="bdr-ec-empty">' . esc_html__( 'Aucun message pour le moment.', 'bdr-banque-en-ligne' ) . '</p>';
		}
		foreach ( $messages as $message ) {
			$mine  = (int) $message->author_id === $me;
			$html .= '<div class="bdr-ec-msg' . ( $mine ? ' bdr-ec-msg--mine' : '' ) . '">'
				. '<div class="bdr-ec-msg__meta"><strong>' . esc_html( BDR_EC_Data::author_label( $message->author_id, $id ) ) . '</strong> · ' . self::format_date( $message->created_at ) . '</div>'
				. '<div class="bdr-ec-msg__body" dir="auto">' . nl2br( esc_html( $message->body ) ) . '</div></div>';
		}
		$html .= '</div>';

		if ( $writable ) {
			$html .= '<form class="bdr-ec-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
				. '<input type="hidden" name="action" value="bdr_ec_message" />'
				. '<input type="hidden" name="dossier" value="' . esc_attr( $id ) . '" />'
				. wp_nonce_field( 'bdr_ec_message_' . $id, '_bdr_ec_nonce', false, false )
				. '<label for="bdr-ec-message">' . esc_html__( 'Votre message', 'bdr-banque-en-ligne' ) . '</label>'
				. '<textarea id="bdr-ec-message" name="message" dir="auto" rows="4" maxlength="5000" required></textarea>'
				. '<p class="bdr-ec-small">' . esc_html__( 'Ne communiquez jamais vos mots de passe ou codes de carte, même à votre conseiller.', 'bdr-banque-en-ligne' ) . '</p>'
				. '<button type="submit" class="bdr-ec-btn">' . esc_html__( 'Envoyer', 'bdr-banque-en-ligne' ) . '</button></form>';
		}
		$html .= '</section>';

		// Documents.
		$html .= '<section class="bdr-ec-section"><h3>' . esc_html__( 'Documents', 'bdr-banque-en-ligne' ) . '</h3>';
		if ( $docs ) {
			$html .= '<table class="bdr-ec-table"><thead><tr><th>' . esc_html__( 'Fichier', 'bdr-banque-en-ligne' ) . '</th><th>' . esc_html__( 'Déposé par', 'bdr-banque-en-ligne' ) . '</th><th>' . esc_html__( 'Date', 'bdr-banque-en-ligne' ) . '</th><th>' . esc_html__( 'Taille', 'bdr-banque-en-ligne' ) . '</th></tr></thead><tbody>';
			foreach ( $docs as $doc ) {
				$html .= '<tr><td><a dir="auto" href="' . esc_url( BDR_EC_Actions::download_url( $doc->id ) ) . '">' . esc_html( $doc->original_name ) . '</a></td>'
					. '<td>' . esc_html( BDR_EC_Data::author_label( $doc->uploader_id, $id ) ) . '</td>'
					. '<td>' . self::format_date( $doc->created_at ) . '</td>'
					. '<td>' . esc_html( size_format( $doc->size, 1 ) ) . '</td></tr>';
			}
			$html .= '</tbody></table>';
		} else {
			$html .= '<p class="bdr-ec-empty">' . esc_html__( 'Aucun document pour le moment.', 'bdr-banque-en-ligne' ) . '</p>';
		}

		if ( $writable ) {
			$exts  = implode( ',', array_map( function ( $ext ) {
				return '.' . $ext;
			}, array_keys( BDR_EC_Storage::allowed_types() ) ) );
			$html .= '<form class="bdr-ec-form" method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
				. '<input type="hidden" name="action" value="bdr_ec_upload" />'
				. '<input type="hidden" name="dossier" value="' . esc_attr( $id ) . '" />'
				. wp_nonce_field( 'bdr_ec_upload_' . $id, '_bdr_ec_nonce', false, false )
				. '<label for="bdr-ec-document">' . esc_html__( 'Déposer un document', 'bdr-banque-en-ligne' ) . '</label>'
				. '<input type="file" id="bdr-ec-document" name="document" accept="' . esc_attr( $exts ) . '" required />'
				. '<p class="bdr-ec-small">' . esc_html(
					sprintf(
						/* translators: %s: max file size */
						__( 'Formats acceptés : PDF, JPG, PNG. Taille maximale : %s.', 'bdr-banque-en-ligne' ),
						size_format( BDR_EC_Storage::max_bytes() )
					)
				) . '</p>'
				. '<button type="submit" class="bdr-ec-btn">' . esc_html__( 'Déposer', 'bdr-banque-en-ligne' ) . '</button></form>';
		}
		$html .= '</section>';

		// Show the latest messages first when the thread is scrollable.
		$html .= '<script>document.querySelectorAll(".bdr-ec-thread").forEach(function(t){t.scrollTop=t.scrollHeight;});</script>';

		return $html;
	}
}
