<?php
/**
 * Display shortcodes for the online banking page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EB_Shortcodes {

	public static function init() {
		add_shortcode( 'bdr_banque_en_ligne', array( __CLASS__, 'full_page' ) );
		add_shortcode( 'bdr_eb_bouton', array( __CLASS__, 'button' ) );
		add_shortcode( 'bdr_eb_applications', array( __CLASS__, 'apps' ) );
		add_shortcode( 'bdr_eb_securite', array( __CLASS__, 'security' ) );
		add_shortcode( 'bdr_eb_assistance', array( __CLASS__, 'support' ) );
	}

	private static function section( $class, $title, $content ) {
		if ( '' === $content ) {
			return '';
		}
		return '<section class="bdr-eb-section bdr-eb-section--' . esc_attr( $class ) . '"><h2>' . esc_html( $title ) . '</h2>' . $content . '</section>';
	}

	/**
	 * [bdr_banque_en_ligne] — every block in one page.
	 */
	public static function full_page() {
		wp_enqueue_style( 'bdr-banque-en-ligne' );

		$html  = '<div class="bdr-eb">';
		$html .= self::section( 'access', __( 'Accéder à votre espace client', 'bdr-banque-en-ligne' ), self::button( array() ) );
		$html .= self::section( 'apps', __( 'Applications mobiles', 'bdr-banque-en-ligne' ), self::apps() );
		$html .= self::section( 'security', __( 'Votre sécurité', 'bdr-banque-en-ligne' ), self::security() );
		$html .= self::section( 'faq', __( 'Questions fréquentes', 'bdr-banque-en-ligne' ), BDR_EB_FAQ::render() );
		$html .= self::section( 'signup', __( 'Demander l\'accès à la banque en ligne', 'bdr-banque-en-ligne' ), BDR_EB_Signup::render( array() ) );
		$html .= self::section( 'support', __( 'Besoin d\'aide ?', 'bdr-banque-en-ligne' ), self::support() );
		$html .= '</div>';

		return $html;
	}

	/**
	 * [bdr_eb_bouton texte="..."]
	 */
	public static function button( $atts ) {
		// The button leads to the client space page of this site.
		if ( ! class_exists( 'BDR_EC_Admin' ) || ! (int) BDR_EC_Admin::get( 'page_id' ) ) {
			return '';
		}
		$url  = BDR_EC_Front::space_url();
		$atts = shortcode_atts( array( 'texte' => BDR_EB_Settings::text( 'button_label' ) ), $atts, 'bdr_eb_bouton' );
		wp_enqueue_style( 'bdr-banque-en-ligne' );

		$lock = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 8V7a3 3 0 1 1 6 0v3H9z"/></svg>';

		return '<a class="bdr-eb-btn" href="' . esc_url( $url ) . '">' . $lock . '<span>' . esc_html( $atts['texte'] ) . '</span></a>';
	}

	/**
	 * [bdr_eb_applications]
	 */
	public static function apps() {
		$stores = array(
			'android_url' => __( 'Disponible sur Google Play', 'bdr-banque-en-ligne' ),
			'ios_url'     => __( 'Télécharger dans l\'App Store', 'bdr-banque-en-ligne' ),
		);
		$links  = '';
		foreach ( $stores as $key => $label ) {
			$url = BDR_EB_Settings::get( $key );
			if ( $url ) {
				$links .= '<a class="bdr-eb-store" href="' . esc_url( $url, array( 'https' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . '</a>';
			}
		}
		if ( '' === $links ) {
			return '';
		}
		wp_enqueue_style( 'bdr-banque-en-ligne' );
		return '<div class="bdr-eb-stores">' . $links . '</div>';
	}

	/**
	 * [bdr_eb_securite]
	 */
	public static function security() {
		$tips = array_filter( array_map( 'trim', explode( "\n", (string) BDR_EB_Settings::text( 'security_tips' ) ) ) );
		if ( ! $tips ) {
			return '';
		}
		wp_enqueue_style( 'bdr-banque-en-ligne' );

		$html = '<ul class="bdr-eb-tips">';
		foreach ( $tips as $tip ) {
			$html .= '<li>' . esc_html( $tip ) . '</li>';
		}
		return $html . '</ul>';
	}

	/**
	 * [bdr_eb_assistance]
	 */
	public static function support() {
		$phone = BDR_EB_Settings::get( 'support_phone' );
		$email = BDR_EB_Settings::get( 'support_email' );
		$hours = BDR_EB_Settings::text( 'support_hours' );

		$items = '';
		if ( $phone ) {
			$tel    = preg_replace( '/[^0-9+]/', '', $phone );
			$items .= '<li><strong>' . esc_html__( 'Téléphone :', 'bdr-banque-en-ligne' ) . '</strong> <a href="' . esc_url( 'tel:' . $tel, array( 'tel' ) ) . '">' . esc_html( $phone ) . '</a></li>';
		}
		if ( $email ) {
			$items .= '<li><strong>' . esc_html__( 'E-mail :', 'bdr-banque-en-ligne' ) . '</strong> <a href="' . esc_url( 'mailto:' . antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></li>';
		}
		if ( $hours ) {
			$items .= '<li><strong>' . esc_html__( 'Horaires :', 'bdr-banque-en-ligne' ) . '</strong> ' . esc_html( $hours ) . '</li>';
		}
		if ( '' === $items ) {
			return '';
		}
		wp_enqueue_style( 'bdr-banque-en-ligne' );
		return '<ul class="bdr-eb-support">' . $items . '</ul>';
	}
}
