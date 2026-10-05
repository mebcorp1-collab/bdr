<?php
/**
 * BDR-NET login: username + password + security code, then the client space.
 *
 * Order of checks on every attempt: IP rate limit, honeypot, nonce, security code, and only then the
 * password (wp_signon, which also applies the per-account lockout of BDR_EC_Actions).
 * Client accounts can only log in through this form: wp-login.php refuses them, so the security
 * code cannot be bypassed.
 *
 * Works with the bdr-modern theme (online banking page and /fr/, /en/, /ar/ routes) and on its own.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_NET_Login {

	const MAX_FAILURES_PER_IP = 30; // per 10 minutes. Only failures count: a branch may share one IP.

	/** @var bool True while this form is logging someone in. */
	private static $via_form = false;

	public static function init() {
		add_action( 'admin_post_nopriv_bdr_net_login', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_bdr_net_login', array( __CLASS__, 'handle' ) );
		add_filter( 'authenticate', array( __CLASS__, 'clients_use_this_form' ), 100 );
		add_action( 'wp', array( __CLASS__, 'load_route_language' ) );
		add_action( 'template_redirect', array( __CLASS__, 'no_cache' ) );
	}

	/* ---------------------------------------------------------------------
	 * Language: follows the theme's /fr/, /en/, /ar/ routes when present
	 * ------------------------------------------------------------------- */

	public static function lang() {
		if ( isset( $_REQUEST['bdr_net_lang'] ) ) {
			$lang = sanitize_key( wp_unslash( $_REQUEST['bdr_net_lang'] ) );
			if ( in_array( $lang, array( 'fr', 'en', 'ar' ), true ) ) {
				return $lang;
			}
		}
		if ( function_exists( 'bdr_v11_lang' ) && did_action( 'wp' ) ) {
			return bdr_v11_lang();
		}
		$locale = determine_locale();
		return 0 === strpos( $locale, 'ar' ) ? 'ar' : ( 0 === strpos( $locale, 'en' ) ? 'en' : 'fr' );
	}

	private static function locale_for( $lang ) {
		return 'ar' === $lang ? 'ar' : ( 'en' === $lang ? 'en_US' : 'fr_FR' );
	}

	/**
	 * Load the plugin translation matching a language, whatever the site language is.
	 */
	public static function use_language( $lang ) {
		unload_textdomain( 'bdr-banque-en-ligne' );
		$mo = BDR_EB_DIR . 'languages/bdr-banque-en-ligne-' . self::locale_for( $lang ) . '.mo';
		if ( 'fr' !== $lang && file_exists( $mo ) ) {
			load_textdomain( 'bdr-banque-en-ligne', $mo );
		}
	}

	public static function load_route_language() {
		if ( function_exists( 'bdr_v11_lang' ) && ! is_admin() ) {
			self::use_language( bdr_v11_lang() );
		}
	}

	/* ---------------------------------------------------------------------
	 * URLs
	 * ------------------------------------------------------------------- */

	/** The page that shows the login: the theme's online banking page, or the client space page. */
	public static function page_url( $lang = null ) {
		$lang = $lang ? $lang : self::lang();
		if ( function_exists( 'bdr_v11_url' ) && function_exists( 'bdr_v15_render_online_page' ) ) {
			return bdr_v11_url( 'banque-en-ligne', $lang );
		}
		return BDR_EC_Front::space_url( 0, array(), $lang );
	}

	private static function current_url() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$url  = set_url_scheme( 'http://' . $host . $uri );
		return remove_query_arg( array( 'bdr_net_err', 'bdr_net_cap' ), wp_validate_redirect( $url, self::page_url() ) );
	}

	public static function is_login_page() {
		return function_exists( 'bdr_v16_is_online_route' ) && bdr_v16_is_online_route();
	}

	public static function no_cache() {
		if ( self::is_login_page() ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			nocache_headers();
		}
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------- */

	private static function icon( $name ) {
		if ( function_exists( 'bdr_v15_icon' ) ) {
			return bdr_v15_icon( $name );
		}
		$paths = array(
			'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
			'lock'    => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
			'eye'     => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
			'refresh' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
			'shield'  => '<path d="M12 3l8 3v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6z"/>',
		);
		return '<svg class="ic" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( isset( $paths[ $name ] ) ? $paths[ $name ] : '' ) . '</svg>';
	}

	private static function messages() {
		return array(
			'empty'    => __( 'Renseignez votre identifiant, votre mot de passe et le code de sécurité.', 'bdr-banque-en-ligne' ),
			'captcha'  => __( 'Code de sécurité incorrect ou expiré. Un nouveau code a été généré.', 'bdr-banque-en-ligne' ),
			'invalid'  => __( 'Identifiant ou mot de passe incorrect.', 'bdr-banque-en-ligne' ),
			'locked'   => __( 'Trop de tentatives de connexion. Pour votre sécurité, réessayez dans 15 minutes ou contactez votre agence.', 'bdr-banque-en-ligne' ),
			'rate'     => __( 'Trop de tentatives. Patientez quelques minutes avant de réessayer.', 'bdr-banque-en-ligne' ),
			'expired'  => __( 'La page a expiré. Veuillez réessayer.', 'bdr-banque-en-ligne' ),
			'noaccess' => __( 'Ce compte n\'a pas accès à l\'espace client.', 'bdr-banque-en-ligne' ),
			'loggedout' => __( 'Vous êtes déconnecté. À bientôt.', 'bdr-banque-en-ligne' ),
		);
	}

	/**
	 * Inner HTML of the login card (the theme wraps it in its own .login-card).
	 */
	public static function render_card( $lang = null ) {
		$lang = $lang ? $lang : self::lang();
		self::use_language( $lang );
		$name = BDR_EC_Admin::get( 'space_name' );

		/* translators: %s: name of the client space, e.g. BDR-NET */
		$html = '<h2 id="bdr-net">' . esc_html( sprintf( __( 'Connexion à %s', 'bdr-banque-en-ligne' ), $name ) ) . '</h2>';

		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$html .= '<p class="login-lead">' . esc_html(
				sprintf(
					/* translators: 1: user name, 2: name of the client space */
					__( 'Bonjour %1$s, vous êtes connecté à %2$s.', 'bdr-banque-en-ligne' ),
					$user->display_name,
					$name
				)
			) . '</p>';
			$html .= '<a class="btn btn-primary btn-lg btn-block" href="' . esc_url( BDR_EC_Front::space_url( 0, array(), $lang ) ) . '">' . self::icon( 'lock' ) . ' <span>' . esc_html__( 'Accéder à mon espace client', 'bdr-banque-en-ligne' ) . '</span></a>';
			$html .= '<p class="field-help"><a href="' . esc_url( wp_logout_url( add_query_arg( 'bdr_net_err', 'loggedout', self::page_url( $lang ) ) ) ) . '">' . esc_html__( 'Se déconnecter', 'bdr-banque-en-ligne' ) . '</a></p>';
			return $html;
		}

		$code     = isset( $_GET['bdr_net_err'] ) ? sanitize_key( wp_unslash( $_GET['bdr_net_err'] ) ) : '';
		$messages = self::messages();
		$kind     = isset( $_GET['bdr_net_cap'] ) && 'text' === $_GET['bdr_net_cap'] ? 'text' : 'image';
		$captcha  = BDR_NET_Captcha::create( $kind );
		$here     = self::current_url();

		if ( isset( $messages[ $code ] ) ) {
			$class = 'loggedout' === $code ? 'is-ok' : 'is-warn';
			$html .= '<p class="login-msg ' . $class . '" role="alert">' . esc_html( $messages[ $code ] ) . '</p>';
		}

		$html .= '<form class="login-form bdr-net-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" autocomplete="on">'
			. '<input type="hidden" name="action" value="bdr_net_login" />'
			. '<input type="hidden" name="bdr_net_lang" value="' . esc_attr( $lang ) . '" />'
			. '<input type="hidden" name="bdr_net_back" value="' . esc_url( $here ) . '" />'
			. '<input type="hidden" name="bdr_net_kind" value="' . esc_attr( $kind ) . '" />'
			. '<input type="hidden" name="bdr_net_token" value="' . esc_attr( $captcha['token'] ) . '" />'
			. wp_nonce_field( 'bdr_net_login', '_bdr_net', false, false );

		$html .= '<div class="field"><label for="bdr-net-user">' . esc_html__( 'Identifiant', 'bdr-banque-en-ligne' ) . '</label>'
			. '<div class="field-in">' . self::icon( 'user' ) . '<input id="bdr-net-user" name="log" type="text" autocomplete="username" autocapitalize="none" spellcheck="false" required></div></div>';

		$html .= '<div class="field"><label for="bdr-net-pass">' . esc_html__( 'Mot de passe', 'bdr-banque-en-ligne' ) . '</label>'
			. '<div class="field-in">' . self::icon( 'lock' ) . '<input id="bdr-net-pass" name="pwd" type="password" autocomplete="current-password" required>'
			. '<button type="button" class="field-toggle" data-bdr-net-eye="bdr-net-pass" aria-controls="bdr-net-pass" aria-label="' . esc_attr__( 'Afficher le mot de passe', 'bdr-banque-en-ligne' ) . '" data-label-show="' . esc_attr__( 'Afficher le mot de passe', 'bdr-banque-en-ligne' ) . '" data-label-hide="' . esc_attr__( 'Masquer le mot de passe', 'bdr-banque-en-ligne' ) . '">' . self::icon( 'eye' ) . '</button></div></div>';

		$html .= '<div class="field captcha-field"><label for="bdr-net-cap">' . esc_html__( 'Code de sécurité', 'bdr-banque-en-ligne' ) . '</label><div class="captcha-box"><div class="captcha-view">';
		if ( 'text' === $captcha['kind'] ) {
			$html .= '<p class="captcha-question">' . esc_html( $captcha['question'] ) . '</p>';
		} else {
			$html .= '<img src="' . esc_attr( $captcha['image'] ) . '" width="240" height="76" alt="' . esc_attr__( 'Code de sécurité : recopiez les 5 caractères de l\'image', 'bdr-banque-en-ligne' ) . '">';
		}
		$html .= '</div><a class="icon-btn captcha-refresh" href="' . esc_url( add_query_arg( 'bdr_net_cap', $kind, $here ) . '#bdr-net' ) . '" aria-label="' . esc_attr__( 'Nouveau code', 'bdr-banque-en-ligne' ) . '" title="' . esc_attr__( 'Nouveau code', 'bdr-banque-en-ligne' ) . '">' . self::icon( 'refresh' ) . '</a></div>'
			. '<input id="bdr-net-cap" name="bdr_net_answer" class="captcha-input" type="text" inputmode="' . ( 'text' === $kind ? 'numeric' : 'text' ) . '" autocomplete="off" autocapitalize="' . ( 'text' === $kind ? 'off' : 'characters' ) . '" spellcheck="false" maxlength="12" required>'
			. '<p class="field-help"><a class="linklike" href="' . esc_url( add_query_arg( 'bdr_net_cap', 'text' === $kind ? 'image' : 'text', $here ) . '#bdr-net' ) . '">'
			. esc_html( 'text' === $kind ? __( 'Revenir au code en image', 'bdr-banque-en-ligne' ) : __( 'Code illisible ? Utiliser une question simple', 'bdr-banque-en-ligne' ) ) . '</a></p></div>';

		$html .= '<div class="bdr-hp" aria-hidden="true"><label>Website <input type="text" name="bdr_net_website" tabindex="-1" autocomplete="off"></label></div>';
		$html .= '<button class="btn btn-primary btn-lg btn-block" type="submit">' . self::icon( 'lock' ) . ' <span>' . esc_html__( 'Se connecter', 'bdr-banque-en-ligne' ) . '</span></button>';
		$html .= '<p class="field-help"><a href="' . esc_url( wp_lostpassword_url( self::page_url( $lang ) ) ) . '">' . esc_html__( 'Mot de passe oublié ?', 'bdr-banque-en-ligne' ) . '</a> · ' . esc_html__( 'Pas encore d\'accès ? Demandez-le à votre agence.', 'bdr-banque-en-ligne' ) . '</p>';
		$html .= '</form>';
		$html .= '<p class="login-foot">' . self::icon( 'shield' ) . ' <span>' . esc_html__( 'Connexion chiffrée. La BDR ne vous demandera jamais votre mot de passe par e-mail, SMS ou téléphone.', 'bdr-banque-en-ligne' ) . '</span></p>';

		return $html;
	}

	/* ---------------------------------------------------------------------
	 * Form handling
	 * ------------------------------------------------------------------- */

	private static function ip_key() {
		return 'bdr_net_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	}

	private static function fail( $code, $back, $kind ) {
		if ( 'rate' !== $code ) {
			set_transient( self::ip_key(), (int) get_transient( self::ip_key() ) + 1, 10 * MINUTE_IN_SECONDS );
		}
		$args = array( 'bdr_net_err' => $code );
		if ( 'text' === $kind ) {
			$args['bdr_net_cap'] = 'text';
		}
		wp_safe_redirect( add_query_arg( $args, $back ) . '#bdr-net' );
		exit;
	}

	public static function handle() {
		$lang = self::lang();
		$back = isset( $_POST['bdr_net_back'] ) ? esc_url_raw( wp_unslash( $_POST['bdr_net_back'] ) ) : '';
		$back = remove_query_arg( array( 'bdr_net_err', 'bdr_net_cap' ), wp_validate_redirect( $back, self::page_url( $lang ) ) );
		$kind = isset( $_POST['bdr_net_kind'] ) && 'text' === $_POST['bdr_net_kind'] ? 'text' : 'image';

		if ( is_user_logged_in() ) {
			wp_safe_redirect( BDR_EC_Front::space_url( 0, array(), $lang ) );
			exit;
		}

		if ( (int) get_transient( self::ip_key() ) >= self::MAX_FAILURES_PER_IP ) {
			self::fail( 'rate', $back, $kind );
		}

		if ( ! empty( $_POST['bdr_net_website'] ) ) {
			self::fail( 'invalid', $back, $kind );
		}
		if ( ! isset( $_POST['_bdr_net'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_bdr_net'] ) ), 'bdr_net_login' ) ) {
			self::fail( 'expired', $back, $kind );
		}

		$login    = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '';
		$password = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- passwords are not sanitized.
		$answer   = isset( $_POST['bdr_net_answer'] ) ? sanitize_text_field( wp_unslash( $_POST['bdr_net_answer'] ) ) : '';
		$token    = isset( $_POST['bdr_net_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bdr_net_token'] ) ) : '';

		if ( '' === $login || '' === $password || '' === $answer ) {
			self::fail( 'empty', $back, $kind );
		}
		if ( ! BDR_NET_Captcha::check( $token, $answer ) ) {
			self::fail( 'captcha', $back, $kind );
		}

		self::$via_form = true;
		$user           = wp_signon(
			array(
				'user_login'    => $login,
				'user_password' => $password,
				'remember'      => false,
			),
			is_ssl()
		);
		self::$via_form = false;

		if ( is_wp_error( $user ) ) {
			self::fail( 'bdr_ec_locked' === $user->get_error_code() ? 'locked' : 'invalid', $back, $kind );
		}
		if ( ! BDR_EC_Data::is_staff( $user->ID ) && ! in_array( 'bdr_client', (array) $user->roles, true ) ) {
			wp_logout();
			self::fail( 'noaccess', $back, $kind );
		}

		wp_safe_redirect( BDR_EC_Front::space_url( 0, array(), $lang ) );
		exit;
	}

	/**
	 * Client accounts must log in with the security code: refuse them on wp-login.php, XML-RPC, etc.
	 */
	public static function clients_use_this_form( $user ) {
		if ( self::$via_form || ! ( $user instanceof WP_User ) ) {
			return $user;
		}
		if ( in_array( 'bdr_client', (array) $user->roles, true ) && ! user_can( $user, 'bdr_ec_manage' ) ) {
			return new WP_Error(
				'bdr_net_use_form',
				sprintf(
					/* translators: %s: link to the client space login page */
					__( 'Pour votre sécurité, connectez-vous depuis la page %s, avec le code de sécurité.', 'bdr-banque-en-ligne' ),
					'<a href="' . esc_url( self::page_url() ) . '">' . esc_html( BDR_EC_Admin::get( 'space_name' ) ) . '</a>'
				)
			);
		}
		return $user;
	}
}

/**
 * Template tag for the bdr-modern theme: inner HTML of the BDR-NET login card.
 */
function bdr_net_login_card( $lang = null ) {
	return BDR_NET_Login::render_card( $lang );
}
