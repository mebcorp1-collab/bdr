<?php
/**
 * Shortcodes that display the company's contact details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_Shortcodes {

	public static function init() {
		add_shortcode( 'bdr_info', array( __CLASS__, 'info' ) );
		add_shortcode( 'bdr_contact_info', array( __CLASS__, 'contact_info' ) );
		add_shortcode( 'bdr_social', array( __CLASS__, 'social' ) );
		add_shortcode( 'bdr_whatsapp', array( __CLASS__, 'whatsapp' ) );
	}

	public static function whatsapp_url() {
		$number = BDR_Settings::international_number( BDR_Settings::get( 'whatsapp' ) );
		if ( '' === $number ) {
			return '';
		}
		$url     = 'https://wa.me/' . $number;
		$message = BDR_Settings::get( 'whatsapp_message' );
		if ( '' !== $message ) {
			$url .= '?text=' . rawurlencode( $message );
		}
		return $url;
	}

	public static function phone_url() {
		$number = BDR_Settings::international_number( BDR_Settings::get( 'phone' ) );
		return '' === $number ? '' : 'tel:+' . $number;
	}

	/**
	 * [bdr_info field="phone" link="yes"]
	 */
	public static function info( $atts ) {
		$atts  = shortcode_atts(
			array(
				'field' => 'phone',
				'link'  => 'yes',
			),
			$atts,
			'bdr_info'
		);
		$field = sanitize_key( $atts['field'] );
		if ( ! in_array( $field, array( 'company_name', 'phone', 'whatsapp', 'email', 'address', 'hours' ), true ) ) {
			return '';
		}

		$value = BDR_Settings::get( $field );
		if ( '' === $value ) {
			return '';
		}
		$link = 'yes' === $atts['link'];

		if ( $link && 'phone' === $field && self::phone_url() ) {
			return '<a href="' . esc_url( self::phone_url() ) . '">' . esc_html( $value ) . '</a>';
		}
		if ( $link && 'whatsapp' === $field && self::whatsapp_url() ) {
			return '<a href="' . esc_url( self::whatsapp_url() ) . '" target="_blank" rel="noopener">' . esc_html( $value ) . '</a>';
		}
		if ( $link && 'email' === $field ) {
			return '<a href="' . esc_url( 'mailto:' . antispambot( $value ) ) . '">' . esc_html( antispambot( $value ) ) . '</a>';
		}
		return nl2br( esc_html( $value ) );
	}

	/**
	 * [bdr_contact_info]
	 */
	public static function contact_info() {
		wp_enqueue_style( 'bdr-toolkit' );

		$rows = array(
			'phone'    => __( 'Téléphone', 'bdr-toolkit' ),
			'whatsapp' => __( 'WhatsApp', 'bdr-toolkit' ),
			'email'    => __( 'E-mail', 'bdr-toolkit' ),
			'address'  => __( 'Adresse', 'bdr-toolkit' ),
			'hours'    => __( 'Horaires', 'bdr-toolkit' ),
		);

		$html = '<ul class="bdr-contact-info">';
		foreach ( $rows as $field => $label ) {
			$value = self::info( array( 'field' => $field ) );
			if ( '' !== $value ) {
				$html .= '<li class="bdr-contact-info__' . esc_attr( $field ) . '"><strong>' . esc_html( $label ) . ' :</strong> <span>' . $value . '</span></li>';
			}
		}
		$html .= '</ul>';

		return $html . self::social();
	}

	/**
	 * [bdr_social]
	 */
	public static function social() {
		wp_enqueue_style( 'bdr-toolkit' );

		$networks = array(
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'linkedin'  => 'LinkedIn',
		);
		$links    = '';
		foreach ( $networks as $key => $label ) {
			$url = BDR_Settings::get( $key );
			if ( $url ) {
				$links .= '<a class="bdr-social__link bdr-social__link--' . esc_attr( $key ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
			}
		}
		return $links ? '<div class="bdr-social">' . $links . '</div>' : '';
	}

	/**
	 * [bdr_whatsapp text="Écrivez-nous sur WhatsApp"]
	 */
	public static function whatsapp( $atts ) {
		$atts = shortcode_atts(
			array( 'text' => __( 'Écrivez-nous sur WhatsApp', 'bdr-toolkit' ) ),
			$atts,
			'bdr_whatsapp'
		);
		$url  = self::whatsapp_url();
		if ( '' === $url ) {
			return '';
		}
		wp_enqueue_style( 'bdr-toolkit' );
		return '<a class="bdr-btn bdr-btn--whatsapp" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $atts['text'] ) . '</a>';
	}
}
