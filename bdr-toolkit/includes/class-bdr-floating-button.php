<?php
/**
 * Floating WhatsApp / call button shown on every front-end page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_Floating_Button {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render' ) );
	}

	/**
	 * Links to display, keyed by type. Empty when the button is disabled or numbers are missing.
	 */
	private static function links() {
		if ( ! BDR_Settings::get( 'button_enabled' ) ) {
			return array();
		}
		$type  = BDR_Settings::get( 'button_type' );
		$links = array();
		if ( in_array( $type, array( 'whatsapp', 'both' ), true ) && BDR_Shortcodes::whatsapp_url() ) {
			$links['whatsapp'] = BDR_Shortcodes::whatsapp_url();
		}
		if ( in_array( $type, array( 'phone', 'both' ), true ) && BDR_Shortcodes::phone_url() ) {
			$links['phone'] = BDR_Shortcodes::phone_url();
		}
		return $links;
	}

	public static function enqueue() {
		if ( ! self::links() ) {
			return;
		}
		wp_enqueue_style( 'bdr-toolkit' );
		wp_add_inline_style( 'bdr-toolkit', '.bdr-float{--bdr-color:' . BDR_Settings::get( 'button_color' ) . ';}' );
	}

	public static function render() {
		$links = self::links();
		if ( ! $links ) {
			return;
		}

		$icons  = array(
			'whatsapp' => '<svg viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M16 3C8.8 3 3 8.7 3 15.8c0 2.3.6 4.5 1.8 6.4L3 29l7-1.8c1.8 1 3.9 1.5 6 1.5 7.2 0 13-5.7 13-12.8S23.2 3 16 3zm0 23.4c-1.9 0-3.8-.5-5.4-1.5l-.4-.2-4.1 1.1 1.1-4-.3-.4a10.5 10.5 0 0 1-1.6-5.6C5.3 10 10.1 5.3 16 5.3S26.7 10 26.7 15.8 21.9 26.4 16 26.4zm5.9-7.9c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2l-1 1.2c-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-.9-1.6-1.9-1.8-2.2-.2-.3 0-.5.1-.7l.5-.6.3-.5v-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.7s1.2 3.1 1.4 3.4c.2.2 2.3 3.5 5.6 4.9 2.8 1.1 3.3.9 3.9.8.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>',
			'phone'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>',
		);
		$labels = array(
			'whatsapp' => __( 'Contactez-nous sur WhatsApp', 'bdr-toolkit' ),
			'phone'    => __( 'Appelez-nous', 'bdr-toolkit' ),
		);

		$position = 'left' === BDR_Settings::get( 'button_position' ) ? 'left' : 'right';

		echo '<div class="bdr-float bdr-float--' . esc_attr( $position ) . '">';
		foreach ( $links as $type => $url ) {
			$target = 'whatsapp' === $type ? ' target="_blank" rel="noopener"' : '';
			printf(
				'<a class="bdr-float__btn bdr-float__btn--%1$s" href="%2$s"%3$s aria-label="%4$s" title="%4$s">%5$s</a>',
				esc_attr( $type ),
				esc_url( $url, array( 'https', 'tel' ) ),
				$target, // Static string.
				esc_attr( $labels[ $type ] ),
				$icons[ $type ] // Static SVG markup.
			);
		}
		echo '</div>';
	}
}
