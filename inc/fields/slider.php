<?php
defined( 'ABSPATH' ) || die;

/**
 * The slider field which users jQueryUI slider widget.
 */
class RWMB_Slider_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_register_style( 'jquery-ui-core', "$url/core.css", [], '1.13.2' );
		wp_style_add_data( 'jquery-ui-core', 'path', RWMB_CSS_DIR . 'jqueryui/core.css' );

		wp_register_style( 'jquery-ui-theme', "$url/theme.css", [], '1.13.2' );
		wp_style_add_data( 'jquery-ui-theme', 'path', RWMB_CSS_DIR . 'jqueryui/theme.css' );

		wp_register_style( 'jquery-ui-slider', "$url/slider.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );
		wp_style_add_data( 'jquery-ui-slider', 'path', RWMB_CSS_DIR . 'jqueryui/slider.css' );

		wp_enqueue_style( 'rwmb-slider', RWMB_CSS_URL . 'slider.css', [ 'jquery-ui-slider' ], RWMB_VER );
		wp_style_add_data( 'rwmb-slider', 'path', RWMB_CSS_DIR . 'slider.css' );
		wp_enqueue_script( 'rwmb-slider', RWMB_JS_URL . 'slider.js', [ 'jquery-ui-slider', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-core' ], RWMB_VER, true );
	}

	/**
	 * Get div HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::call( 'get_attributes', $field, $meta );
		return sprintf(
			'<div class="rwmb-slider-inner">
				<div class="rwmb-slider-ui" id="%s" data-options="%s"></div>
				<span class="rwmb-slider-label">%s<span>%s</span>%s</span>
				<input type="hidden" value="%s" %s>
			</div>',
			$field['id'],
			esc_attr( wp_json_encode( $field['js_options'] ) ),
			$field['prefix'],
			$meta,
			$field['suffix'],
			$meta,
			self::render_attributes( $attributes )
		);
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field               = parent::normalize( $field );
		$field               = wp_parse_args( $field, [
			'prefix'     => '',
			'suffix'     => '',
			'std'        => '',
			'js_options' => [],
		] );
		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'range' => 'min', // range = 'min' will add a dark background to sliding part, better UI.
			'value' => $field['std'],
		] );

		return $field;
	}
}
