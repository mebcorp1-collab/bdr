<?php
/**
 * Private file storage for client documents.
 *
 * Files are saved with random names and no extension, in a folder that is never linked publicly:
 * - preferably outside the web root, by defining BDR_EC_STORAGE_DIR in wp-config.php;
 * - otherwise in wp-content/uploads/bdr-espace-client-<random>/, protected by .htaccess / web.config.
 * They are only ever served through BDR_EC_Actions::download(), after an access check.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EC_Storage {

	const SUFFIX_OPTION = 'bdr_ec_storage_suffix';

	/**
	 * Allowed extensions and their real MIME types (checked against the file content).
	 */
	public static function allowed_types() {
		return apply_filters(
			'bdr_ec_allowed_types',
			array(
				'pdf'  => 'application/pdf',
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
			)
		);
	}

	public static function dir() {
		if ( defined( 'BDR_EC_STORAGE_DIR' ) && BDR_EC_STORAGE_DIR ) {
			return untrailingslashit( BDR_EC_STORAGE_DIR );
		}
		$suffix = get_option( self::SUFFIX_OPTION );
		if ( ! $suffix ) {
			$suffix = strtolower( wp_generate_password( 24, false ) );
			update_option( self::SUFFIX_OPTION, $suffix, false );
		}
		$uploads = wp_upload_dir( null, false );
		return $uploads['basedir'] . '/bdr-espace-client-' . $suffix;
	}

	public static function is_outside_webroot() {
		return defined( 'BDR_EC_STORAGE_DIR' ) && BDR_EC_STORAGE_DIR && 0 !== strpos( wp_normalize_path( BDR_EC_STORAGE_DIR ), wp_normalize_path( ABSPATH ) );
	}

	public static function ensure_dir() {
		$dir = self::dir();
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$guards = array(
			'.htaccess'  => "# BDR Espace Client: private files, never served directly.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n",
			'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
			'index.php'  => "<?php\n// Silence is golden.\n",
		);
		foreach ( $guards as $name => $content ) {
			if ( ! file_exists( $dir . '/' . $name ) ) {
				file_put_contents( $dir . '/' . $name, $content ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			}
		}
		return is_dir( $dir ) && is_writable( $dir );
	}

	public static function path( $stored_name ) {
		// Stored names are 32 hex characters; refuse anything else so no path can escape the folder.
		if ( ! preg_match( '/^[a-f0-9]{32}$/', (string) $stored_name ) ) {
			return '';
		}
		return self::dir() . '/' . $stored_name;
	}

	public static function max_bytes() {
		$mb = (int) BDR_EC_Admin::get( 'max_size_mb' );
		$mb = $mb > 0 ? $mb : 10;
		return min( $mb * MB_IN_BYTES, wp_max_upload_size() );
	}

	/**
	 * Validate and store an uploaded file. Returns [ 'original', 'stored', 'mime', 'size' ] or WP_Error.
	 */
	public static function store_upload( $file ) {
		if ( ! is_array( $file ) || ! isset( $file['error'], $file['tmp_name'], $file['name'], $file['size'] ) ) {
			return new WP_Error( 'bdr_ec_no_file', __( 'Aucun fichier reçu.', 'bdr-espace-client' ) );
		}
		if ( UPLOAD_ERR_INI_SIZE === $file['error'] || UPLOAD_ERR_FORM_SIZE === $file['error'] ) {
			return new WP_Error( 'bdr_ec_too_big', __( 'Le fichier est trop volumineux.', 'bdr-espace-client' ) );
		}
		if ( UPLOAD_ERR_OK !== $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'bdr_ec_upload', __( 'Le fichier n\'a pas pu être reçu. Veuillez réessayer.', 'bdr-espace-client' ) );
		}
		if ( (int) $file['size'] <= 0 || (int) $file['size'] > self::max_bytes() ) {
			return new WP_Error( 'bdr_ec_too_big', __( 'Le fichier est trop volumineux.', 'bdr-espace-client' ) );
		}

		$original = sanitize_file_name( wp_basename( $file['name'] ) );
		$ext      = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );
		$allowed  = self::allowed_types();
		if ( ! isset( $allowed[ $ext ] ) ) {
			return new WP_Error( 'bdr_ec_type', __( 'Type de fichier non autorisé.', 'bdr-espace-client' ) );
		}

		// Check the real content, not just the name.
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime  = $finfo ? finfo_file( $finfo, $file['tmp_name'] ) : '';
		if ( $finfo ) {
			finfo_close( $finfo );
		}
		if ( $mime !== $allowed[ $ext ] ) {
			return new WP_Error( 'bdr_ec_type', __( 'Le contenu du fichier ne correspond pas à son type.', 'bdr-espace-client' ) );
		}

		if ( ! self::ensure_dir() ) {
			return new WP_Error( 'bdr_ec_storage', __( 'Le stockage des documents n\'est pas disponible. Contactez la banque.', 'bdr-espace-client' ) );
		}

		$stored = bin2hex( random_bytes( 16 ) );
		$target = self::path( $stored );
		if ( ! move_uploaded_file( $file['tmp_name'], $target ) ) {
			return new WP_Error( 'bdr_ec_storage', __( 'Le fichier n\'a pas pu être enregistré.', 'bdr-espace-client' ) );
		}
		chmod( $target, 0640 );

		return array(
			'original' => $original,
			'stored'   => $stored,
			'mime'     => $mime,
			'size'     => (int) $file['size'],
		);
	}
}
