<?php
/**
 * Online banking subscription requests: [bdr_eb_adhesion].
 *
 * Collects contact details only. No account number, card number, identifier or password is ever requested.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EB_Signup {

	const POST_TYPE = 'bdr_eb_demande';
	const NONCE     = 'bdr_eb_nonce';

	/** @var array|null Result of the current submission. */
	private static $result = null;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'handle_submission' ) );
		add_shortcode( 'bdr_eb_adhesion', array( __CLASS__, 'render' ) );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_status' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'          => __( 'Demandes d\'adhésion', 'bdr-banque-en-ligne' ),
					'singular_name' => __( 'Demande', 'bdr-banque-en-ligne' ),
					'menu_name'     => __( 'Demandes d\'adhésion', 'bdr-banque-en-ligne' ),
					'all_items'     => __( 'Demandes d\'adhésion', 'bdr-banque-en-ligne' ),
					'edit_item'     => __( 'Traiter la demande', 'bdr-banque-en-ligne' ),
					'search_items'  => __( 'Rechercher', 'bdr-banque-en-ligne' ),
					'not_found'     => __( 'Aucune demande pour le moment.', 'bdr-banque-en-ligne' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => BDR_EB_Settings::MENU,
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}

	private static function fields() {
		return array(
			'client_type' => __( 'Type de client', 'bdr-banque-en-ligne' ),
			'name'        => __( 'Nom et prénom / Raison sociale', 'bdr-banque-en-ligne' ),
			'phone'       => __( 'Téléphone mobile', 'bdr-banque-en-ligne' ),
			'email'       => __( 'E-mail', 'bdr-banque-en-ligne' ),
			'agency'      => __( 'Agence de domiciliation', 'bdr-banque-en-ligne' ),
			'message'     => __( 'Message', 'bdr-banque-en-ligne' ),
		);
	}

	private static function client_types() {
		return array(
			'particulier'   => __( 'Particulier', 'bdr-banque-en-ligne' ),
			'professionnel' => __( 'Professionnel', 'bdr-banque-en-ligne' ),
			'entreprise'    => __( 'Entreprise', 'bdr-banque-en-ligne' ),
		);
	}

	private static function statuses() {
		return array(
			'nouvelle' => __( 'Nouvelle', 'bdr-banque-en-ligne' ),
			'en_cours' => __( 'En cours', 'bdr-banque-en-ligne' ),
			'traitee'  => __( 'Traitée', 'bdr-banque-en-ligne' ),
			'refusee'  => __( 'Refusée', 'bdr-banque-en-ligne' ),
		);
	}

	public static function handle_submission() {
		if ( empty( $_POST['bdr_eb_submit'] ) || ! BDR_EB_Settings::get( 'signup_enabled' ) ) {
			return;
		}

		$values = array();
		foreach ( array_keys( self::fields() ) as $key ) {
			$raw            = isset( $_POST[ 'bdr_eb_' . $key ] ) ? wp_unslash( $_POST[ 'bdr_eb_' . $key ] ) : '';
			$values[ $key ] = 'message' === $key ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		}
		$values['email'] = sanitize_email( $values['email'] );

		// Honeypot.
		if ( ! empty( $_POST['bdr_eb_website'] ) ) {
			self::$result = array(
				'status' => 'success',
				'values' => array(),
				'errors' => array(),
			);
			return;
		}

		$errors = array();
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), 'bdr_eb_signup' ) ) {
			$errors[] = __( 'La session a expiré. Veuillez recharger la page et réessayer.', 'bdr-banque-en-ligne' );
		}

		$ip_key = 'bdr_eb_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		if ( get_transient( $ip_key ) ) {
			$errors[] = __( 'Une demande vient déjà d\'être envoyée. Veuillez patienter quelques minutes.', 'bdr-banque-en-ligne' );
		}

		if ( ! array_key_exists( $values['client_type'], self::client_types() ) ) {
			$errors[] = __( 'Veuillez choisir le type de client.', 'bdr-banque-en-ligne' );
		}
		if ( '' === $values['name'] ) {
			$errors[] = __( 'Veuillez indiquer votre nom ou votre raison sociale.', 'bdr-banque-en-ligne' );
		}
		if ( ! preg_match( '/^(?:\+213|00213|0)[5-7]\d{8}$/', preg_replace( '/[\s.-]+/', '', $values['phone'] ) ) ) {
			$errors[] = __( 'Veuillez indiquer un numéro de mobile algérien valide (ex. : 0555 12 34 56).', 'bdr-banque-en-ligne' );
		}
		if ( ! is_email( $values['email'] ) ) {
			$errors[] = __( 'Veuillez indiquer une adresse e-mail valide.', 'bdr-banque-en-ligne' );
		}
		if ( '' === $values['agency'] ) {
			$errors[] = __( 'Veuillez indiquer votre agence.', 'bdr-banque-en-ligne' );
		}
		if ( empty( $_POST['bdr_eb_consent'] ) ) {
			$errors[] = __( 'Veuillez accepter d\'être contacté par la banque.', 'bdr-banque-en-ligne' );
		}
		// Refuse messages that look like they contain a card or account number, so they never get stored or e-mailed.
		if ( self::contains_long_number( $values['message'] ) ) {
			$errors[] = __( 'Pour votre sécurité, n\'indiquez aucun numéro de compte ou de carte dans le message.', 'bdr-banque-en-ligne' );
		}

		if ( $errors ) {
			self::$result = array(
				'status' => 'error',
				'values' => $values,
				'errors' => $errors,
			);
			return;
		}

		set_transient( $ip_key, 1, 5 * MINUTE_IN_SECONDS );

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'private',
				'post_title'   => $values['name'] . ' — ' . $values['agency'],
				'post_content' => $values['message'],
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( array( 'client_type', 'name', 'phone', 'email', 'agency' ) as $key ) {
				update_post_meta( $post_id, '_bdr_eb_' . $key, $values[ $key ] );
			}
			update_post_meta( $post_id, '_bdr_eb_status', 'nouvelle' );
		}

		self::notify( $values, $post_id );

		self::$result = array(
			'status' => 'success',
			'values' => array(),
			'errors' => array(),
		);
	}

	/**
	 * True when the text holds a run of 14+ digits (card numbers have 16, RIB 20), spaces and dashes allowed.
	 * Phone numbers (10 to 12 digits) are not affected.
	 */
	private static function contains_long_number( $text ) {
		preg_match_all( '/\d[\d\s.-]*\d/', $text, $matches );
		foreach ( $matches[0] as $run ) {
			if ( strlen( preg_replace( '/\D/', '', $run ) ) >= 14 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Notify the bank. The e-mail only links to the request in the admin; personal details stay in WordPress.
	 */
	private static function notify( $values, $post_id ) {
		$to = BDR_EB_Settings::get( 'signup_recipient' );
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}
		$types = self::client_types();

		/* translators: %s: agency name */
		$subject = sprintf( __( '[Banque en ligne] Nouvelle demande d\'adhésion — %s', 'bdr-banque-en-ligne' ), $values['agency'] );
		$body    = __( 'Une nouvelle demande d\'adhésion à la banque en ligne a été déposée.', 'bdr-banque-en-ligne' ) . "\n\n"
			. self::fields()['client_type'] . ' : ' . $types[ $values['client_type'] ] . "\n"
			. self::fields()['agency'] . ' : ' . $values['agency'] . "\n\n"
			. __( 'Consulter la demande :', 'bdr-banque-en-ligne' ) . ' ' . admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' );

		return wp_mail( $to, $subject, $body );
	}

	public static function render( $atts ) {
		if ( ! BDR_EB_Settings::get( 'signup_enabled' ) ) {
			return '';
		}
		wp_enqueue_style( 'bdr-banque-en-ligne' );

		$result = self::$result;
		$values = $result ? $result['values'] : array();
		$labels = self::fields();
		$value  = function ( $key ) use ( $values ) {
			return isset( $values[ $key ] ) ? $values[ $key ] : '';
		};

		ob_start();
		?>
		<div class="bdr-eb-form-wrap" id="bdr-eb-adhesion">
			<?php if ( $result && 'success' === $result['status'] ) : ?>
				<div class="bdr-eb-notice bdr-eb-notice--success" role="status"><?php echo esc_html( BDR_EB_Settings::get( 'signup_success' ) ); ?></div>
			<?php else : ?>
				<?php if ( $result && $result['errors'] ) : ?>
					<div class="bdr-eb-notice bdr-eb-notice--error" role="alert"><ul>
						<?php foreach ( $result['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul></div>
				<?php endif; ?>

				<p class="bdr-eb-warning"><?php esc_html_e( 'Ce formulaire ne vous demandera jamais vos identifiants, mots de passe, numéro de compte ou de carte.', 'bdr-banque-en-ligne' ); ?></p>

				<form class="bdr-eb-form" method="post" action="#bdr-eb-adhesion" novalidate>
					<?php wp_nonce_field( 'bdr_eb_signup', self::NONCE ); ?>
					<fieldset class="bdr-eb-field">
						<legend><?php echo esc_html( $labels['client_type'] ); ?> *</legend>
						<?php foreach ( self::client_types() as $type => $type_label ) : ?>
							<label class="bdr-eb-radio"><input type="radio" name="bdr_eb_client_type" value="<?php echo esc_attr( $type ); ?>" <?php checked( $value( 'client_type' ), $type ); ?> required /> <?php echo esc_html( $type_label ); ?></label>
						<?php endforeach; ?>
					</fieldset>
					<div class="bdr-eb-row">
						<p class="bdr-eb-field">
							<label for="bdr_eb_name"><?php echo esc_html( $labels['name'] ); ?> *</label>
							<input type="text" id="bdr_eb_name" name="bdr_eb_name" required autocomplete="name" value="<?php echo esc_attr( $value( 'name' ) ); ?>" />
						</p>
						<p class="bdr-eb-field">
							<label for="bdr_eb_agency"><?php echo esc_html( $labels['agency'] ); ?> *</label>
							<input type="text" id="bdr_eb_agency" name="bdr_eb_agency" required value="<?php echo esc_attr( $value( 'agency' ) ); ?>" />
						</p>
					</div>
					<div class="bdr-eb-row">
						<p class="bdr-eb-field">
							<label for="bdr_eb_phone"><?php echo esc_html( $labels['phone'] ); ?> *</label>
							<input type="tel" id="bdr_eb_phone" name="bdr_eb_phone" required autocomplete="tel" placeholder="0555 12 34 56" value="<?php echo esc_attr( $value( 'phone' ) ); ?>" />
						</p>
						<p class="bdr-eb-field">
							<label for="bdr_eb_email"><?php echo esc_html( $labels['email'] ); ?> *</label>
							<input type="email" id="bdr_eb_email" name="bdr_eb_email" required autocomplete="email" value="<?php echo esc_attr( $value( 'email' ) ); ?>" />
						</p>
					</div>
					<p class="bdr-eb-field">
						<label for="bdr_eb_message"><?php echo esc_html( $labels['message'] ); ?></label>
						<textarea id="bdr_eb_message" name="bdr_eb_message" rows="4"><?php echo esc_textarea( $value( 'message' ) ); ?></textarea>
					</p>
					<p class="bdr-eb-hp" aria-hidden="true">
						<label for="bdr_eb_website">Website</label>
						<input type="text" id="bdr_eb_website" name="bdr_eb_website" tabindex="-1" autocomplete="off" />
					</p>
					<p class="bdr-eb-field">
						<label class="bdr-eb-radio"><input type="checkbox" name="bdr_eb_consent" value="1" required /> <?php esc_html_e( 'J\'accepte que la banque utilise ces informations pour me recontacter au sujet de ma demande.', 'bdr-banque-en-ligne' ); ?> *</label>
					</p>
					<p><button type="submit" name="bdr_eb_submit" value="1" class="bdr-eb-btn"><?php esc_html_e( 'Envoyer ma demande', 'bdr-banque-en-ligne' ); ?></button></p>
				</form>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function columns( $columns ) {
		return array(
			'cb'         => $columns['cb'],
			'title'      => __( 'Demandeur', 'bdr-banque-en-ligne' ),
			'bdr_type'   => __( 'Type', 'bdr-banque-en-ligne' ),
			'bdr_phone'  => __( 'Téléphone', 'bdr-banque-en-ligne' ),
			'bdr_status' => __( 'Statut', 'bdr-banque-en-ligne' ),
			'date'       => $columns['date'],
		);
	}

	public static function column_content( $column, $post_id ) {
		if ( 'bdr_type' === $column ) {
			$types = self::client_types();
			$type  = get_post_meta( $post_id, '_bdr_eb_client_type', true );
			echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : '—' );
		} elseif ( 'bdr_phone' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_bdr_eb_phone', true ) );
		} elseif ( 'bdr_status' === $column ) {
			$statuses = self::statuses();
			$status   = get_post_meta( $post_id, '_bdr_eb_status', true );
			echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : $statuses['nouvelle'] );
		}
	}

	public static function meta_boxes() {
		add_meta_box( 'bdr_eb_details', __( 'Détails de la demande', 'bdr-banque-en-ligne' ), array( __CLASS__, 'render_details' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'bdr_eb_status', __( 'Statut', 'bdr-banque-en-ligne' ), array( __CLASS__, 'render_status' ), self::POST_TYPE, 'side', 'high' );
	}

	public static function render_details( $post ) {
		$labels = self::fields();
		$types  = self::client_types();

		echo '<table class="form-table"><tbody>';
		foreach ( array( 'client_type', 'name', 'phone', 'email', 'agency' ) as $key ) {
			$meta = get_post_meta( $post->ID, '_bdr_eb_' . $key, true );
			if ( 'client_type' === $key && isset( $types[ $meta ] ) ) {
				$meta = $types[ $meta ];
			}
			printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $labels[ $key ] ), esc_html( $meta ) );
		}
		printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $labels['message'] ), nl2br( esc_html( $post->post_content ) ) );
		echo '</tbody></table>';
	}

	public static function render_status( $post ) {
		wp_nonce_field( 'bdr_eb_status', 'bdr_eb_status_nonce' );
		$current = get_post_meta( $post->ID, '_bdr_eb_status', true );
		echo '<select name="bdr_eb_status" style="width:100%">';
		foreach ( self::statuses() as $status => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $status ), selected( $current, $status, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	public static function save_status( $post_id ) {
		if ( ! isset( $_POST['bdr_eb_status_nonce'], $_POST['bdr_eb_status'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bdr_eb_status_nonce'] ) ), 'bdr_eb_status' )
			|| ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$status = sanitize_key( wp_unslash( $_POST['bdr_eb_status'] ) );
		if ( array_key_exists( $status, self::statuses() ) ) {
			update_post_meta( $post_id, '_bdr_eb_status', $status );
		}
	}
}
