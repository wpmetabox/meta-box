<?php
/**
 * The slider field which users jQueryUI slider widget.
 *
 * @package Meta Box
 */

/**
 * Slider field class.
 */
class RWMB_Slider_Field extends RWMB_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_enqueue_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
		wp_enqueue_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );
		wp_enqueue_style( 'jquery-ui-slider', "{$url}/jquery.ui.slider.css", array(), '1.8.17' );
		wp_enqueue_style( 'rwmb-slider', RWMB_CSS_URL . 'slider.css' );

		wp_enqueue_script( 'rwmb-slider', RWMB_JS_URL . 'slider.js', array( 'jquery-ui-slider', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-core' ), RWMB_VER, true );
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
		return sprintf(
			'<div class="clearfix">
				<div class="rwmb-slider" id="%s" data-options="%s"></div>
				<span class="rwmb-slider-value-label">%s<span>%s</span>%s</span>
				<input type="hidden" name="%s" value="%s" class="rwmb-slider-value">
			</div>',
			$field['id'], esc_attr( wp_json_encode( $field['js_options'] ) ),
			$field['prefix'], ( $meta >= 0 ) ? $meta : $field['std'], $field['suffix'],
			$field['field_name'], ( $meta >= 0 ) ? $meta : $field['std']
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
		$field               = wp_parse_args( $field, array(
			'prefix'     => '',
			'suffix'     => '',
			'std'        => '',
			'js_options' => array(),
		) );
		$field['js_options'] = wp_parse_args( $field['js_options'], array(
			'range' => 'min', // range = 'min' will add a dark background to sliding part, better UI.
			'value' => $field['std'],
		) );

		return $field;
	}
}
