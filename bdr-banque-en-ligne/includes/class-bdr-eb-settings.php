<?php
/**
 * Admin menu "Banque en ligne" and its settings page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EB_Settings {

	const OPTION = 'bdr_eb_options';
	const MENU   = 'bdr-banque-en-ligne';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_translatable_texts' ) );
	}

	public static function defaults() {
		return array(
			'button_label'    => 'Accéder à mon espace client',
			'android_url'     => '',
			'ios_url'         => '',
			'support_phone'   => '',
			'support_email'   => '',
			'support_hours'   => '',
			'security_tips'   => implode(
				"\n",
				array(
					'La banque ne vous demandera jamais vos identifiants, mots de passe ou codes de confirmation par e-mail, SMS ou téléphone.',
					'Accédez à votre espace client uniquement via le bouton officiel de ce site ou en saisissant vous-même l\'adresse du portail.',
					'Vérifiez que l\'adresse commence par https:// et que le cadenas s\'affiche dans votre navigateur.',
					'Ne cliquez jamais sur un lien reçu par message pour vous connecter à votre banque.',
					'Ne communiquez jamais vos codes, même à un conseiller de la banque.',
					'Déconnectez-vous après chaque session, surtout sur un ordinateur partagé.',
					'En cas de doute ou d\'opération suspecte, contactez immédiatement votre agence.',
				)
			),
			'signup_enabled'   => 1,
			'signup_recipient' => get_option( 'admin_email' ),
			'signup_success'   => 'Votre demande d\'adhésion a bien été enregistrée. Votre agence vous contactera pour finaliser l\'activation de votre accès.',
		);
	}

	/**
	 * Texts typed in the settings that visitors read, translatable per language
	 * with Polylang (Languages > Translations) or WPML (String Translation, see wpml-config.xml).
	 */
	public static function translatable_texts() {
		return array(
			'button_label'   => false,
			'support_hours'  => false,
			'security_tips'  => true,
			'signup_success' => true,
		);
	}

	public static function register_translatable_texts() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
		foreach ( self::translatable_texts() as $key => $multiline ) {
			$value = self::get( $key );
			if ( '' !== $value ) {
				pll_register_string( 'bdr_eb_' . $key, $value, 'BDR Banque en ligne', $multiline );
			}
		}
	}

	/**
	 * A setting text in the visitor's language (falls back to the saved text).
	 */
	public static function text( $key ) {
		$value = self::get( $key );
		return function_exists( 'pll__' ) ? pll__( $value ) : $value;
	}

	public static function get( $key = null ) {
		$options = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		if ( null === $key ) {
			return $options;
		}
		return isset( $options[ $key ] ) ? $options[ $key ] : '';
	}

	public static function add_menu() {
		// Advisors (no manage_options) must still reach "Dossiers clients" under this menu:
		// WordPress denies a submenu page when the user cannot open its parent menu.
		add_menu_page(
			__( 'Banque en ligne', 'bdr-banque-en-ligne' ),
			__( 'Banque en ligne', 'bdr-banque-en-ligne' ),
			BDR_EB_CLIENT_SPACE ? 'edit_bdr_dossiers' : 'manage_options',
			self::MENU,
			array( __CLASS__, 'render_page' ),
			'dashicons-bank',
			25
		);
		add_submenu_page( self::MENU, __( 'Réglages', 'bdr-banque-en-ligne' ), __( 'Réglages', 'bdr-banque-en-ligne' ), 'manage_options', self::MENU, array( __CLASS__, 'render_page' ) );
	}

	public static function register() {
		register_setting(
			'bdr_eb',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section( 'bdr_eb_portal', __( 'Bouton d\'accès à l\'espace client', 'bdr-banque-en-ligne' ), array( __CLASS__, 'portal_help' ), self::MENU );
		self::field( 'button_label', __( 'Texte du bouton', 'bdr-banque-en-ligne' ), 'text', 'bdr_eb_portal' );

		add_settings_section( 'bdr_eb_apps', __( 'Applications mobiles', 'bdr-banque-en-ligne' ), '__return_false', self::MENU );
		self::field( 'android_url', __( 'Lien Google Play', 'bdr-banque-en-ligne' ), 'url', 'bdr_eb_apps' );
		self::field( 'ios_url', __( 'Lien App Store', 'bdr-banque-en-ligne' ), 'url', 'bdr_eb_apps' );

		add_settings_section( 'bdr_eb_support', __( 'Assistance banque en ligne', 'bdr-banque-en-ligne' ), '__return_false', self::MENU );
		self::field( 'support_phone', __( 'Téléphone', 'bdr-banque-en-ligne' ), 'text', 'bdr_eb_support' );
		self::field( 'support_email', __( 'E-mail', 'bdr-banque-en-ligne' ), 'email', 'bdr_eb_support' );
		self::field( 'support_hours', __( 'Horaires', 'bdr-banque-en-ligne' ), 'text', 'bdr_eb_support' );

		add_settings_section( 'bdr_eb_security', __( 'Sécurité', 'bdr-banque-en-ligne' ), '__return_false', self::MENU );
		self::field( 'security_tips', __( 'Conseils de sécurité', 'bdr-banque-en-ligne' ), 'textarea', 'bdr_eb_security', __( 'Un conseil par ligne.', 'bdr-banque-en-ligne' ) );

		add_settings_section( 'bdr_eb_signup', __( 'Demandes d\'adhésion', 'bdr-banque-en-ligne' ), '__return_false', self::MENU );
		self::field( 'signup_enabled', __( 'Activer le formulaire', 'bdr-banque-en-ligne' ), 'checkbox', 'bdr_eb_signup' );
		self::field( 'signup_recipient', __( 'E-mail de notification', 'bdr-banque-en-ligne' ), 'email', 'bdr_eb_signup' );
		self::field( 'signup_success', __( 'Message de confirmation', 'bdr-banque-en-ligne' ), 'textarea', 'bdr_eb_signup' );
	}

	private static function field( $key, $label, $type, $section, $help = '' ) {
		add_settings_field(
			'bdr_eb_' . $key,
			$label,
			array( __CLASS__, 'render_field' ),
			self::MENU,
			$section,
			array(
				'key'       => $key,
				'type'      => $type,
				'help'      => $help,
				'label_for' => 'bdr_eb_' . $key,
			)
		);
	}

	public static function render_field( $args ) {
		$id    = 'bdr_eb_' . $args['key'];
		$name  = self::OPTION . '[' . $args['key'] . ']';
		$value = self::get( $args['key'] );

		if ( 'textarea' === $args['type'] ) {
			$rows = 'security_tips' === $args['key'] ? 8 : 3;
			printf( '<textarea id="%s" name="%s" rows="%d" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), (int) $rows, esc_textarea( $value ) );
		} elseif ( 'checkbox' === $args['type'] ) {
			printf( '<input type="checkbox" id="%s" name="%s" value="1" %s />', esc_attr( $id ), esc_attr( $name ), checked( 1, (int) $value, false ) );
		} else {
			printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr( $args['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
		}

		if ( $args['help'] ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}

	/**
	 * Accept only https URLs for anything clients will click to reach the bank.
	 */
	private static function https_url( $url ) {
		$url = esc_url_raw( trim( (string) $url ), array( 'https' ) );
		return 0 === strpos( $url, 'https://' ) ? $url : '';
	}

	public static function sanitize( $input ) {
		$input = (array) $input;
		$clean = array();

		foreach ( array( 'android_url', 'ios_url' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? self::https_url( $input[ $key ] ) : '';
		}

		foreach ( array( 'button_label', 'support_phone', 'support_hours' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}
		foreach ( array( 'security_tips', 'signup_success' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : '';
		}
		foreach ( array( 'support_email', 'signup_recipient' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_email( $input[ $key ] ) : '';
		}
		$clean['signup_enabled'] = empty( $input['signup_enabled'] ) ? 0 : 1;

		return $clean;
	}

	public static function portal_help() {
		echo '<p>' . esc_html__( 'Le bouton mène à la page de l\'espace client (choisie plus bas, section « Espace client »), où les clients se connectent pour suivre leurs dossiers, déposer leurs documents et écrire à leur conseiller.', 'bdr-banque-en-ligne' ) . '</p>';
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Banque en ligne', 'bdr-banque-en-ligne' ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'bdr_eb' );
				do_settings_sections( self::MENU );
				submit_button();
				?>
			</form>

			<?php
			if ( class_exists( 'BDR_EC_Admin' ) ) {
				BDR_EC_Admin::render_settings_section();
			}
			?>

			<h2><?php esc_html_e( 'Shortcodes', 'bdr-banque-en-ligne' ); ?></h2>
			<table class="widefat striped" style="max-width:860px">
				<tbody>
					<tr><td><code>[bdr_espace_client]</code></td><td><?php esc_html_e( 'Espace client : connexion, dossiers, messagerie et documents', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_banque_en_ligne]</code></td><td><?php esc_html_e( 'Page complète : accès, applications, sécurité, FAQ, adhésion, assistance', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_bouton]</code></td><td><?php esc_html_e( 'Bouton d\'accès à l\'espace client (option : texte="...")', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_applications]</code></td><td><?php esc_html_e( 'Liens vers les applications mobiles', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_securite]</code></td><td><?php esc_html_e( 'Conseils de sécurité', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_faq]</code></td><td><?php esc_html_e( 'Questions fréquentes (menu Banque en ligne > FAQ)', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_adhesion]</code></td><td><?php esc_html_e( 'Formulaire de demande d\'adhésion', 'bdr-banque-en-ligne' ); ?></td></tr>
					<tr><td><code>[bdr_eb_assistance]</code></td><td><?php esc_html_e( 'Coordonnées de l\'assistance', 'bdr-banque-en-ligne' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
