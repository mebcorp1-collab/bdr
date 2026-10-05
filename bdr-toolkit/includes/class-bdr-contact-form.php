<?php
/**
 * Contact form shortcode [bdr_contact_form] with e-mail notification and message history in the admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_Contact_Form {

	const POST_TYPE = 'bdr_message';
	const NONCE     = 'bdr_contact_nonce';

	/** @var array Result of the current submission: status, message, errors, values. */
	private static $result = null;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'handle_submission' ) );
		add_shortcode( 'bdr_contact_form', array( __CLASS__, 'render' ) );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Messages BDR', 'bdr-toolkit' ),
					'singular_name' => __( 'Message', 'bdr-toolkit' ),
					'menu_name'     => __( 'Messages BDR', 'bdr-toolkit' ),
					'all_items'     => __( 'Tous les messages', 'bdr-toolkit' ),
					'edit_item'     => __( 'Voir le message', 'bdr-toolkit' ),
					'search_items'  => __( 'Rechercher', 'bdr-toolkit' ),
					'not_found'     => __( 'Aucun message pour le moment.', 'bdr-toolkit' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 26,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}

	private static function fields() {
		return array(
			'name'    => __( 'Nom complet', 'bdr-toolkit' ),
			'email'   => __( 'E-mail', 'bdr-toolkit' ),
			'phone'   => __( 'Téléphone', 'bdr-toolkit' ),
			'subject' => __( 'Objet', 'bdr-toolkit' ),
			'message' => __( 'Message', 'bdr-toolkit' ),
		);
	}

	public static function handle_submission() {
		if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) || empty( $_POST['bdr_contact_submit'] ) ) {
			return;
		}

		$values = array();
		foreach ( array_keys( self::fields() ) as $key ) {
			$raw            = isset( $_POST[ 'bdr_' . $key ] ) ? wp_unslash( $_POST[ 'bdr_' . $key ] ) : '';
			$values[ $key ] = 'message' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		}
		$values['email'] = sanitize_email( $values['email'] );

		$errors = array();

		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), 'bdr_contact' ) ) {
			$errors[] = __( 'La session a expiré. Veuillez recharger la page et réessayer.', 'bdr-toolkit' );
		}

		// Honeypot: bots fill every field, humans never see this one.
		if ( ! empty( $_POST['bdr_website'] ) ) {
			self::$result = array(
				'status' => 'success',
				'values' => array(),
				'errors' => array(),
			);
			return;
		}

		// Simple rate limit: one message per IP every 60 seconds.
		$ip_key = 'bdr_cf_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		if ( get_transient( $ip_key ) ) {
			$errors[] = __( 'Vous avez déjà envoyé un message. Veuillez patienter une minute.', 'bdr-toolkit' );
		}

		if ( '' === $values['name'] ) {
			$errors[] = __( 'Veuillez indiquer votre nom.', 'bdr-toolkit' );
		}
		if ( ! is_email( $values['email'] ) ) {
			$errors[] = __( 'Veuillez indiquer une adresse e-mail valide.', 'bdr-toolkit' );
		}
		if ( '' !== $values['phone'] && ! preg_match( '/^[0-9 +().-]{8,20}$/', $values['phone'] ) ) {
			$errors[] = __( 'Le numéro de téléphone n\'est pas valide.', 'bdr-toolkit' );
		}
		if ( mb_strlen( $values['message'] ) < 10 ) {
			$errors[] = __( 'Votre message doit contenir au moins 10 caractères.', 'bdr-toolkit' );
		}

		if ( $errors ) {
			self::$result = array(
				'status' => 'error',
				'values' => $values,
				'errors' => $errors,
			);
			return;
		}

		set_transient( $ip_key, 1, MINUTE_IN_SECONDS );

		$title   = '' !== $values['subject'] ? $values['subject'] : __( '(sans objet)', 'bdr-toolkit' );
		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => $values['name'] . ' — ' . $title,
				'post_content' => $values['message'],
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( array( 'name', 'email', 'phone', 'subject' ) as $key ) {
				update_post_meta( $post_id, '_bdr_' . $key, $values[ $key ] );
			}
		}

		self::send_email( $values, $title );

		self::$result = array(
			'status' => 'success',
			'values' => array(),
			'errors' => array(),
		);
	}

	private static function send_email( $values, $title ) {
		$to = BDR_Settings::get( 'form_recipient' );
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		/* translators: %s: message subject */
		$subject = sprintf( __( '[Site BDR] Nouveau message : %s', 'bdr-toolkit' ), $title );

		$body = '';
		foreach ( self::fields() as $key => $label ) {
			$body .= $label . ' : ' . ( 'message' === $key ? "\n" : '' ) . $values[ $key ] . "\n";
		}
		$body .= "\n" . __( 'Envoyé depuis', 'bdr-toolkit' ) . ' ' . home_url( '/' );

		$headers = array( 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>' );

		return wp_mail( $to, $subject, $body, $headers );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array( 'button' => __( 'Envoyer', 'bdr-toolkit' ) ),
			$atts,
			'bdr_contact_form'
		);

		wp_enqueue_style( 'bdr-toolkit' );

		$result = self::$result;
		$values = $result ? $result['values'] : array();
		$labels = self::fields();

		ob_start();
		?>
		<div class="bdr-form-wrap" id="bdr-contact">
			<?php if ( $result && 'success' === $result['status'] ) : ?>
				<div class="bdr-notice bdr-notice--success" role="status"><?php echo esc_html( BDR_Settings::get( 'form_success' ) ); ?></div>
			<?php elseif ( $result && $result['errors'] ) : ?>
				<div class="bdr-notice bdr-notice--error" role="alert">
					<ul>
						<?php foreach ( $result['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form class="bdr-form" method="post" action="#bdr-contact" novalidate>
				<?php wp_nonce_field( 'bdr_contact', self::NONCE ); ?>
				<div class="bdr-form__row">
					<p class="bdr-form__field">
						<label for="bdr_name"><?php echo esc_html( $labels['name'] ); ?> <span aria-hidden="true">*</span></label>
						<input type="text" id="bdr_name" name="bdr_name" required autocomplete="name" value="<?php echo esc_attr( isset( $values['name'] ) ? $values['name'] : '' ); ?>" />
					</p>
					<p class="bdr-form__field">
						<label for="bdr_email"><?php echo esc_html( $labels['email'] ); ?> <span aria-hidden="true">*</span></label>
						<input type="email" id="bdr_email" name="bdr_email" required autocomplete="email" value="<?php echo esc_attr( isset( $values['email'] ) ? $values['email'] : '' ); ?>" />
					</p>
				</div>
				<div class="bdr-form__row">
					<p class="bdr-form__field">
						<label for="bdr_phone"><?php echo esc_html( $labels['phone'] ); ?></label>
						<input type="tel" id="bdr_phone" name="bdr_phone" autocomplete="tel" placeholder="05 55 12 34 56" value="<?php echo esc_attr( isset( $values['phone'] ) ? $values['phone'] : '' ); ?>" />
					</p>
					<p class="bdr-form__field">
						<label for="bdr_subject"><?php echo esc_html( $labels['subject'] ); ?></label>
						<input type="text" id="bdr_subject" name="bdr_subject" value="<?php echo esc_attr( isset( $values['subject'] ) ? $values['subject'] : '' ); ?>" />
					</p>
				</div>
				<p class="bdr-form__field">
					<label for="bdr_message"><?php echo esc_html( $labels['message'] ); ?> <span aria-hidden="true">*</span></label>
					<textarea id="bdr_message" name="bdr_message" rows="6" required><?php echo esc_textarea( isset( $values['message'] ) ? $values['message'] : '' ); ?></textarea>
				</p>
				<p class="bdr-form__hp" aria-hidden="true">
					<label for="bdr_website">Website</label>
					<input type="text" id="bdr_website" name="bdr_website" tabindex="-1" autocomplete="off" />
				</p>
				<p>
					<button type="submit" name="bdr_contact_submit" value="1" class="bdr-btn"><?php echo esc_html( $atts['button'] ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function columns( $columns ) {
		return array(
			'cb'        => $columns['cb'],
			'title'     => __( 'Message', 'bdr-toolkit' ),
			'bdr_email' => __( 'E-mail', 'bdr-toolkit' ),
			'bdr_phone' => __( 'Téléphone', 'bdr-toolkit' ),
			'date'      => $columns['date'],
		);
	}

	public static function column_content( $column, $post_id ) {
		if ( 'bdr_email' === $column ) {
			$email = get_post_meta( $post_id, '_bdr_email', true );
			echo $email ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>' : '—';
		} elseif ( 'bdr_phone' === $column ) {
			$phone = get_post_meta( $post_id, '_bdr_phone', true );
			echo $phone ? esc_html( $phone ) : '—';
		}
	}

	public static function meta_box() {
		add_meta_box( 'bdr_message_details', __( 'Détails du message', 'bdr-toolkit' ), array( __CLASS__, 'render_meta_box' ), self::POST_TYPE, 'normal', 'high' );
	}

	public static function render_meta_box( $post ) {
		echo '<table class="form-table"><tbody>';
		foreach ( array( 'name', 'email', 'phone', 'subject' ) as $key ) {
			$labels = self::fields();
			printf(
				'<tr><th scope="row">%s</th><td>%s</td></tr>',
				esc_html( $labels[ $key ] ),
				esc_html( get_post_meta( $post->ID, '_bdr_' . $key, true ) )
			);
		}
		printf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			esc_html__( 'Message', 'bdr-toolkit' ),
			nl2br( esc_html( $post->post_content ) )
		);
		echo '</tbody></table>';
	}
}
