<?php
/**
 * Settings page: Réglages > BDR Toolkit.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_Settings {

	const OPTION = 'bdr_toolkit_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function defaults() {
		return array(
			'company_name'     => 'BDR',
			'phone'            => '',
			'whatsapp'         => '',
			'email'            => get_option( 'admin_email' ),
			'address'          => '',
			'hours'            => '',
			'facebook'         => '',
			'instagram'        => '',
			'linkedin'         => '',
			'button_enabled'   => 1,
			'button_type'      => 'whatsapp',
			'button_position'  => 'right',
			'button_color'     => '#25D366',
			'whatsapp_message' => 'Bonjour, je vous contacte depuis le site bdr-dz.com.',
			'form_recipient'   => get_option( 'admin_email' ),
			'form_success'     => 'Merci ! Votre message a bien été envoyé. Nous vous répondrons rapidement.',
		);
	}

	/**
	 * Get one option value, or all options when $key is null.
	 */
	public static function get( $key = null ) {
		$options = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		if ( null === $key ) {
			return $options;
		}
		return isset( $options[ $key ] ) ? $options[ $key ] : '';
	}

	/**
	 * Keep digits only and convert an Algerian local number (0XXXXXXXXX) to international format (213XXXXXXXXX).
	 */
	public static function international_number( $number ) {
		$digits = preg_replace( '/\D+/', '', (string) $number );
		if ( '' === $digits ) {
			return '';
		}
		if ( 0 === strpos( $digits, '00' ) ) {
			$digits = substr( $digits, 2 );
		} elseif ( 0 === strpos( $digits, '0' ) ) {
			$digits = '213' . substr( $digits, 1 );
		}
		return $digits;
	}

	public static function add_menu() {
		add_options_page(
			__( 'BDR Toolkit', 'bdr-toolkit' ),
			__( 'BDR Toolkit', 'bdr-toolkit' ),
			'manage_options',
			'bdr-toolkit',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register() {
		register_setting(
			'bdr_toolkit',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section( 'bdr_contact', __( 'Coordonnées de l\'entreprise', 'bdr-toolkit' ), '__return_false', 'bdr-toolkit' );
		self::field( 'company_name', __( 'Nom de l\'entreprise', 'bdr-toolkit' ), 'text', 'bdr_contact' );
		self::field( 'phone', __( 'Téléphone', 'bdr-toolkit' ), 'text', 'bdr_contact', __( 'Ex. : 0555 12 34 56', 'bdr-toolkit' ) );
		self::field( 'whatsapp', __( 'Numéro WhatsApp', 'bdr-toolkit' ), 'text', 'bdr_contact', __( 'Ex. : 0555 12 34 56 ou +213 555 12 34 56', 'bdr-toolkit' ) );
		self::field( 'email', __( 'E-mail', 'bdr-toolkit' ), 'email', 'bdr_contact' );
		self::field( 'address', __( 'Adresse', 'bdr-toolkit' ), 'textarea', 'bdr_contact' );
		self::field( 'hours', __( 'Horaires d\'ouverture', 'bdr-toolkit' ), 'textarea', 'bdr_contact', __( 'Ex. : Dim – Jeu : 08h00 – 17h00', 'bdr-toolkit' ) );
		self::field( 'facebook', __( 'Facebook (URL)', 'bdr-toolkit' ), 'url', 'bdr_contact' );
		self::field( 'instagram', __( 'Instagram (URL)', 'bdr-toolkit' ), 'url', 'bdr_contact' );
		self::field( 'linkedin', __( 'LinkedIn (URL)', 'bdr-toolkit' ), 'url', 'bdr_contact' );

		add_settings_section( 'bdr_button', __( 'Bouton flottant', 'bdr-toolkit' ), '__return_false', 'bdr-toolkit' );
		self::field( 'button_enabled', __( 'Afficher le bouton', 'bdr-toolkit' ), 'checkbox', 'bdr_button' );
		self::field(
			'button_type',
			__( 'Type de bouton', 'bdr-toolkit' ),
			'select',
			'bdr_button',
			'',
			array(
				'whatsapp' => __( 'WhatsApp', 'bdr-toolkit' ),
				'phone'    => __( 'Appel téléphonique', 'bdr-toolkit' ),
				'both'     => __( 'WhatsApp + Appel', 'bdr-toolkit' ),
			)
		);
		self::field(
			'button_position',
			__( 'Position', 'bdr-toolkit' ),
			'select',
			'bdr_button',
			'',
			array(
				'right' => __( 'En bas à droite', 'bdr-toolkit' ),
				'left'  => __( 'En bas à gauche', 'bdr-toolkit' ),
			)
		);
		self::field( 'button_color', __( 'Couleur', 'bdr-toolkit' ), 'color', 'bdr_button' );
		self::field( 'whatsapp_message', __( 'Message WhatsApp pré-rempli', 'bdr-toolkit' ), 'textarea', 'bdr_button' );

		add_settings_section( 'bdr_form', __( 'Formulaire de contact', 'bdr-toolkit' ), array( __CLASS__, 'form_section_help' ), 'bdr-toolkit' );
		self::field( 'form_recipient', __( 'E-mail de réception', 'bdr-toolkit' ), 'email', 'bdr_form' );
		self::field( 'form_success', __( 'Message de confirmation', 'bdr-toolkit' ), 'textarea', 'bdr_form' );
	}

	private static function field( $key, $label, $type, $section, $help = '', $choices = array() ) {
		add_settings_field(
			'bdr_' . $key,
			$label,
			array( __CLASS__, 'render_field' ),
			'bdr-toolkit',
			$section,
			array(
				'key'       => $key,
				'type'      => $type,
				'help'      => $help,
				'choices'   => $choices,
				'label_for' => 'bdr_' . $key,
			)
		);
	}

	public static function render_field( $args ) {
		$key   = $args['key'];
		$id    = 'bdr_' . $key;
		$name  = self::OPTION . '[' . $key . ']';
		$value = self::get( $key );

		switch ( $args['type'] ) {
			case 'textarea':
				printf( '<textarea id="%s" name="%s" rows="3" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $value ) );
				break;
			case 'checkbox':
				printf( '<input type="checkbox" id="%s" name="%s" value="1" %s />', esc_attr( $id ), esc_attr( $name ), checked( 1, (int) $value, false ) );
				break;
			case 'select':
				printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $args['choices'] as $choice => $choice_label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $choice_label ) );
				}
				echo '</select>';
				break;
			case 'color':
				printf( '<input type="color" id="%s" name="%s" value="%s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
				break;
			default:
				printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr( $args['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
		}

		if ( $args['help'] ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}

	public static function sanitize( $input ) {
		$input    = (array) $input;
		$defaults = self::defaults();
		$clean    = array();

		foreach ( array( 'company_name', 'phone', 'whatsapp' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}
		foreach ( array( 'address', 'hours', 'whatsapp_message', 'form_success' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : '';
		}
		foreach ( array( 'email', 'form_recipient' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? sanitize_email( $input[ $key ] ) : '';
		}
		foreach ( array( 'facebook', 'instagram', 'linkedin' ) as $key ) {
			$clean[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( $input[ $key ] ) : '';
		}

		$clean['button_enabled']  = empty( $input['button_enabled'] ) ? 0 : 1;
		$clean['button_type']     = isset( $input['button_type'] ) && in_array( $input['button_type'], array( 'whatsapp', 'phone', 'both' ), true ) ? $input['button_type'] : $defaults['button_type'];
		$clean['button_position'] = isset( $input['button_position'] ) && in_array( $input['button_position'], array( 'right', 'left' ), true ) ? $input['button_position'] : $defaults['button_position'];
		$color                    = isset( $input['button_color'] ) ? sanitize_hex_color( $input['button_color'] ) : '';
		$clean['button_color']    = $color ? $color : $defaults['button_color'];

		return $clean;
	}

	public static function form_section_help() {
		echo '<p>' . wp_kses_post(
			__( 'Insérez le formulaire dans une page avec le shortcode <code>[bdr_contact_form]</code>. Les messages reçus sont aussi enregistrés dans le menu <strong>Messages BDR</strong>.', 'bdr-toolkit' )
		) . '</p>';
	}

	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'BDR Toolkit', 'bdr-toolkit' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'bdr_toolkit' );
				do_settings_sections( 'bdr-toolkit' );
				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Shortcodes disponibles', 'bdr-toolkit' ); ?></h2>
			<table class="widefat striped" style="max-width:800px">
				<tbody>
					<tr><td><code>[bdr_contact_form]</code></td><td><?php esc_html_e( 'Formulaire de contact', 'bdr-toolkit' ); ?></td></tr>
					<tr><td><code>[bdr_contact_info]</code></td><td><?php esc_html_e( 'Bloc complet des coordonnées', 'bdr-toolkit' ); ?></td></tr>
					<tr><td><code>[bdr_info field="phone"]</code></td><td><?php esc_html_e( 'Une seule donnée : company_name, phone, whatsapp, email, address, hours', 'bdr-toolkit' ); ?></td></tr>
					<tr><td><code>[bdr_social]</code></td><td><?php esc_html_e( 'Liens vers les réseaux sociaux', 'bdr-toolkit' ); ?></td></tr>
					<tr><td><code>[bdr_whatsapp text="Écrivez-nous"]</code></td><td><?php esc_html_e( 'Bouton WhatsApp en ligne', 'bdr-toolkit' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
