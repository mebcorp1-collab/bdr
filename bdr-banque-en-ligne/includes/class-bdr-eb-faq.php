<?php
/**
 * FAQ entries managed in the admin and displayed with [bdr_eb_faq].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDR_EB_FAQ {

	const POST_TYPE = 'bdr_eb_faq';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_shortcode( 'bdr_eb_faq', array( __CLASS__, 'render' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'FAQ banque en ligne', 'bdr-banque-en-ligne' ),
					'singular_name' => __( 'Question', 'bdr-banque-en-ligne' ),
					'menu_name'     => __( 'FAQ', 'bdr-banque-en-ligne' ),
					'all_items'     => __( 'FAQ', 'bdr-banque-en-ligne' ),
					'add_new'       => __( 'Ajouter une question', 'bdr-banque-en-ligne' ),
					'add_new_item'  => __( 'Nouvelle question', 'bdr-banque-en-ligne' ),
					'edit_item'     => __( 'Modifier la question', 'bdr-banque-en-ligne' ),
					'not_found'     => __( 'Aucune question.', 'bdr-banque-en-ligne' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => BDR_EB_Settings::MENU,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'page-attributes' ),
			)
		);
	}

	/**
	 * [bdr_eb_faq] — questions ordered by "Ordre" (page attributes), then title.
	 */
	public static function render() {
		$questions = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);
		if ( ! $questions ) {
			return '';
		}
		wp_enqueue_style( 'bdr-banque-en-ligne' );

		$html = '<div class="bdr-eb-faq">';
		foreach ( $questions as $question ) {
			$html .= '<details class="bdr-eb-faq__item"><summary>' . esc_html( get_the_title( $question ) ) . '</summary>'
				. '<div class="bdr-eb-faq__answer">' . wp_kses_post( wpautop( $question->post_content ) ) . '</div></details>';
		}
		return $html . '</div>';
	}
}
