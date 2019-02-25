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
		wp_enqueue_style( 'rwmb-slider', RWMB_CSS_URL . 'slider.css', array(), RWMB_VER );

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
		$value = $meta;

		if ( $field['js_options']['range'] ) {
			$meta       = ! is_array( $meta ) ? explode( '|', $meta ) : $meta;
			$value      = implode( '|', $meta );
			$value_text = implode( ' - ', $meta );
		}

		return sprintf(
			'<div class="clearfix">
				<div class="rwmb-slider" id="%s" data-options="%s"></div>
				<span class="rwmb-slider-value-label">%s<span>%s</span>%s</span>
				<input type="hidden" name="%s" value="%s" class="rwmb-slider-value">
			</div>',
			$field['id'],
			esc_attr( wp_json_encode( $field['js_options'] ) ),
			$field['prefix'],
			( is_array( $meta ) ) ? $value_text : $field['std'],
			$field['suffix'],
			$field['field_name'],
			( is_array( $meta ) ) ? $value : $field['std']
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
		$field               = wp_parse_args(
			$field,
			array(
				'prefix'     => '',
				'suffix'     => '',
				'std'        => '',
				'js_options' => array(),
			)
		);
		$field['js_options'] = wp_parse_args(
			$field['js_options'],
			array(
				'range' => 'min', // range = 'min' will add a dark background to sliding part, better UI.
				'value' => $field['std'],
			)
		);

		return $field;
	}

	/**
	 * Get the field value.
	 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
	 * of the field saved in the database, while this function returns more meaningful value of the field, for ex.:
	 * for file/image: return array of file/image information instead of file/image IDs.
	 *
	 * Each field can extend this function and add more data to the returned value.
	 * See specific field classes for details.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		// Some fields does not have ID like heading, custom HTML, etc.
		if ( empty( $field['id'] ) ) {
			return '';
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Get raw meta value in the database, no escape.
		$value = self::call( $field, 'raw_meta', $post_id, $args );
		$value = str_replace( '|', ' - ', $value );

		// Make sure meta value is an array for cloneable and multiple fields.
		if ( $field['clone'] || $field['multiple'] ) {
			$value = is_array( $value ) && $value ? $value : array();
		}

		return $value;
	}

}
