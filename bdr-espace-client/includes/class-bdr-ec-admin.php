<?php
/**
 * Admin side: settings, dossier follow-up box (client, advisor, status) and read-only exchange history.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Admin {

	const OPTION = 'bdr_ec_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_notices', array( __CLASS__, 'setup_notices' ) );
		add_action( 'add_meta_boxes_' . BDR_EC_Data::POST_TYPE, array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . BDR_EC_Data::POST_TYPE, array( __CLASS__, 'save_dossier' ) );
		add_filter( 'manage_' . BDR_EC_Data::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . BDR_EC_Data::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
	}

	public static function defaults() {
		return array(
			'space_name'   => 'BDR-NET',
			'page_id'      => 0,
			'max_size_mb'  => 10,
			'notify_email' => get_option( 'admin_email' ),
		);
	}

	public static function get( $key ) {
		$options = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		return isset( $options[ $key ] ) ? $options[ $key ] : '';
	}

	/* ---------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------- */

	public static function add_menu() {
		add_submenu_page(
			'edit.php?post_type=' . BDR_EC_Data::POST_TYPE,
			__( 'Réglages de l\'espace client', 'bdr-espace-client' ),
			__( 'Réglages', 'bdr-espace-client' ),
			'manage_options',
			'bdr-ec-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	public static function register_settings() {
		register_setting(
			'bdr_ec',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function sanitize( $input ) {
		$input = (array) $input;
		$name  = isset( $input['space_name'] ) ? sanitize_text_field( $input['space_name'] ) : '';
		$email = isset( $input['notify_email'] ) ? sanitize_email( $input['notify_email'] ) : '';
		$size  = isset( $input['max_size_mb'] ) ? absint( $input['max_size_mb'] ) : 10;

		return array(
			'space_name'   => '' !== $name ? $name : 'BDR-NET',
			'page_id'      => isset( $input['page_id'] ) ? absint( $input['page_id'] ) : 0,
			'max_size_mb'  => min( max( $size, 1 ), 50 ),
			'notify_email' => is_email( $email ) ? $email : get_option( 'admin_email' ),
		);
	}

	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opt = self::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Réglages de l\'espace client', 'bdr-espace-client' ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( 'bdr_ec' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="bdr_ec_space_name"><?php esc_html_e( 'Nom de l\'espace', 'bdr-espace-client' ); ?></label></th>
						<td><input type="text" id="bdr_ec_space_name" name="<?php echo esc_attr( $opt ); ?>[space_name]" value="<?php echo esc_attr( self::get( 'space_name' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="bdr_ec_page_id"><?php esc_html_e( 'Page de l\'espace client', 'bdr-espace-client' ); ?></label></th>
						<td>
							<?php
							wp_dropdown_pages(
								array(
									'name'              => esc_attr( $opt ) . '[page_id]',
									'id'                => 'bdr_ec_page_id',
									'selected'          => (int) self::get( 'page_id' ),
									'show_option_none'  => esc_html__( '— Choisir une page —', 'bdr-espace-client' ),
									'option_none_value' => 0,
								)
							);
							?>
							<p class="description"><?php esc_html_e( 'La page qui contient le shortcode [bdr_espace_client]. Elle sert aux liens des e-mails et à la redirection après connexion.', 'bdr-espace-client' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bdr_ec_max"><?php esc_html_e( 'Taille maximale des documents (Mo)', 'bdr-espace-client' ); ?></label></th>
						<td>
							<input type="number" min="1" max="50" id="bdr_ec_max" name="<?php echo esc_attr( $opt ); ?>[max_size_mb]" value="<?php echo esc_attr( self::get( 'max_size_mb' ) ); ?>" class="small-text" />
							<p class="description">
								<?php
								/* translators: %s: server upload limit */
								echo esc_html( sprintf( __( 'Limite du serveur : %s.', 'bdr-espace-client' ), size_format( wp_max_upload_size() ) ) );
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="bdr_ec_notify"><?php esc_html_e( 'E-mail de notification par défaut', 'bdr-espace-client' ); ?></label></th>
						<td>
							<input type="email" id="bdr_ec_notify" name="<?php echo esc_attr( $opt ); ?>[notify_email]" value="<?php echo esc_attr( self::get( 'notify_email' ) ); ?>" class="regular-text" />
							<p class="description"><?php esc_html_e( 'Utilisé quand aucun conseiller n\'est attribué au dossier.', 'bdr-espace-client' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Stockage des documents', 'bdr-espace-client' ); ?></h2>
			<?php if ( BDR_EC_Storage::is_outside_webroot() ) : ?>
				<p><span class="dashicons dashicons-yes" style="color:#008a20"></span> <?php esc_html_e( 'Les documents sont stockés en dehors du dossier public du site (BDR_EC_STORAGE_DIR).', 'bdr-espace-client' ); ?></p>
			<?php else : ?>
				<p><span class="dashicons dashicons-warning" style="color:#dba617"></span>
					<?php esc_html_e( 'Les documents sont stockés dans un dossier protégé de wp-content/uploads. Pour une sécurité maximale, demandez à votre hébergeur de créer un dossier hors du site et ajoutez dans wp-config.php :', 'bdr-espace-client' ); ?>
				</p>
				<pre style="background:#fff;padding:8px;max-width:760px">define( 'BDR_EC_STORAGE_DIR', '/chemin/hors/du/site/bdr-documents' );</pre>
				<p><?php esc_html_e( 'Si votre serveur utilise Nginx, les fichiers .htaccess ne sont pas pris en compte : cette option est alors indispensable.', 'bdr-espace-client' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function setup_notices() {
		if ( ! current_user_can( 'manage_options' ) || (int) self::get( 'page_id' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Espace client : créez une page contenant [bdr_espace_client], puis sélectionnez-la dans les réglages.', 'bdr-espace-client' ),
			esc_url( admin_url( 'edit.php?post_type=' . BDR_EC_Data::POST_TYPE . '&page=bdr-ec-settings' ) ),
			esc_html__( 'Ouvrir les réglages', 'bdr-espace-client' )
		);
	}

	/* ---------------------------------------------------------------------
	 * Dossier screen
	 * ------------------------------------------------------------------- */

	public static function meta_boxes() {
		add_meta_box( 'bdr_ec_follow', __( 'Suivi du dossier', 'bdr-espace-client' ), array( __CLASS__, 'render_follow_box' ), BDR_EC_Data::POST_TYPE, 'side', 'high' );
		add_meta_box( 'bdr_ec_history', __( 'Échanges et documents', 'bdr-espace-client' ), array( __CLASS__, 'render_history_box' ), BDR_EC_Data::POST_TYPE, 'normal', 'high' );
	}

	private static function user_select( $name, $users, $selected, $empty_label ) {
		$html = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" style="width:100%"><option value="0">' . esc_html( $empty_label ) . '</option>';
		foreach ( $users as $user ) {
			$html .= '<option value="' . esc_attr( $user->ID ) . '"' . selected( (int) $selected, (int) $user->ID, false ) . '>' . esc_html( $user->display_name . ' — ' . $user->user_email ) . '</option>';
		}
		return $html . '</select>';
	}

	public static function render_follow_box( $post ) {
		wp_nonce_field( 'bdr_ec_save_dossier', '_bdr_ec_dossier_nonce' );

		$clients  = get_users( array( 'role' => 'bdr_client', 'orderby' => 'display_name', 'number' => 2000, 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
		$advisors = get_users( array( 'role__in' => array( 'administrator', 'bdr_conseiller' ), 'orderby' => 'display_name', 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
		?>
		<p><label for="bdr_ec_client"><strong><?php esc_html_e( 'Client', 'bdr-espace-client' ); ?></strong></label><br />
			<?php echo self::user_select( 'bdr_ec_client', $clients, BDR_EC_Data::client_id( $post->ID ), __( '— Choisir —', 'bdr-espace-client' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in user_select(). ?>
			<?php if ( ! $clients ) : ?>
				<span class="description"><?php esc_html_e( 'Aucun client : créez d\'abord un utilisateur avec le rôle « Client BDR ».', 'bdr-espace-client' ); ?></span>
			<?php endif; ?>
		</p>
		<p><label for="bdr_ec_conseiller"><strong><?php esc_html_e( 'Conseiller', 'bdr-espace-client' ); ?></strong></label><br />
			<?php echo self::user_select( 'bdr_ec_conseiller', $advisors, BDR_EC_Data::advisor_id( $post->ID ), __( '— Aucun —', 'bdr-espace-client' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in user_select(). ?>
		</p>
		<p><label for="bdr_ec_status"><strong><?php esc_html_e( 'Statut', 'bdr-espace-client' ); ?></strong></label><br />
			<select name="bdr_ec_status" id="bdr_ec_status" style="width:100%">
				<?php foreach ( BDR_EC_Data::statuses() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( BDR_EC_Data::status( $post->ID ), $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public static function save_dossier( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['_bdr_ec_dossier_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_bdr_ec_dossier_nonce'] ) ), 'bdr_ec_save_dossier' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) || ! current_user_can( 'bdr_ec_manage' ) ) {
			return;
		}

		$client = isset( $_POST['bdr_ec_client'] ) ? absint( $_POST['bdr_ec_client'] ) : 0;
		$client = $client && user_can( $client, 'read' ) && in_array( 'bdr_client', (array) get_userdata( $client )->roles, true ) ? $client : 0;
		update_post_meta( $post_id, '_bdr_ec_client', $client );

		$advisor = isset( $_POST['bdr_ec_conseiller'] ) ? absint( $_POST['bdr_ec_conseiller'] ) : 0;
		update_post_meta( $post_id, '_bdr_ec_conseiller', $advisor && user_can( $advisor, 'bdr_ec_manage' ) ? $advisor : 0 );

		$status = isset( $_POST['bdr_ec_status'] ) ? sanitize_key( wp_unslash( $_POST['bdr_ec_status'] ) ) : 'ouvert';
		update_post_meta( $post_id, '_bdr_ec_status', array_key_exists( $status, BDR_EC_Data::statuses() ) ? $status : 'ouvert' );
	}

	public static function render_history_box( $post ) {
		if ( 'publish' !== $post->post_status ) {
			echo '<p>' . esc_html__( 'Publiez le dossier pour que le client puisse le voir et échanger avec vous.', 'bdr-espace-client' ) . '</p>';
			return;
		}

		$messages = BDR_EC_Data::messages( $post->ID );
		$docs     = BDR_EC_Data::documents( $post->ID );

		printf(
			'<p><a class="button button-primary" href="%s" target="_blank" rel="noopener">%s</a></p>',
			esc_url( BDR_EC_Front::space_url( $post->ID ) ),
			esc_html__( 'Répondre / déposer un document dans l\'espace client', 'bdr-espace-client' )
		);

		echo '<h4>' . esc_html__( 'Messages', 'bdr-espace-client' ) . '</h4>';
		if ( ! $messages ) {
			echo '<p>' . esc_html__( 'Aucun message.', 'bdr-espace-client' ) . '</p>';
		}
		foreach ( $messages as $message ) {
			printf(
				'<div style="border-inline-start:3px solid #2271b1;padding:4px 10px;margin-bottom:10px"><strong>%s</strong> · %s<div dir="auto">%s</div></div>',
				esc_html( BDR_EC_Data::author_label( $message->author_id, $post->ID ) ),
				BDR_EC_Front::format_date( $message->created_at ),
				nl2br( esc_html( $message->body ) )
			);
		}

		echo '<h4>' . esc_html__( 'Documents', 'bdr-espace-client' ) . '</h4>';
		if ( ! $docs ) {
			echo '<p>' . esc_html__( 'Aucun document.', 'bdr-espace-client' ) . '</p>';
			return;
		}
		echo '<ul>';
		foreach ( $docs as $doc ) {
			printf(
				'<li><a href="%s">%s</a> — %s, %s (%s)</li>',
				esc_url( BDR_EC_Actions::download_url( $doc->id ) ),
				esc_html( $doc->original_name ),
				esc_html( BDR_EC_Data::author_label( $doc->uploader_id, $post->ID ) ),
				BDR_EC_Front::format_date( $doc->created_at ),
				esc_html( size_format( $doc->size, 1 ) )
			);
		}
		echo '</ul>';
	}

	public static function columns( $columns ) {
		return array(
			'cb'            => $columns['cb'],
			'title'         => __( 'Dossier', 'bdr-espace-client' ),
			'bdr_client'    => __( 'Client', 'bdr-espace-client' ),
			'bdr_advisor'   => __( 'Conseiller', 'bdr-espace-client' ),
			'bdr_status'    => __( 'Statut', 'bdr-espace-client' ),
			'bdr_unread'    => __( 'Non lus', 'bdr-espace-client' ),
			'date'          => $columns['date'],
		);
	}

	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'bdr_client':
				$user = get_userdata( BDR_EC_Data::client_id( $post_id ) );
				echo esc_html( $user ? $user->display_name : '—' );
				break;
			case 'bdr_advisor':
				$user = get_userdata( BDR_EC_Data::advisor_id( $post_id ) );
				echo esc_html( $user ? $user->display_name : '—' );
				break;
			case 'bdr_status':
				echo esc_html( BDR_EC_Data::status_label( $post_id ) );
				break;
			case 'bdr_unread':
				$count = BDR_EC_Data::unread_count( $post_id );
				echo $count ? '<strong>' . (int) $count . '</strong>' : '0';
				break;
		}
	}
}
