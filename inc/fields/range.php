<?php
/**
 * The HTML5 range field.
 */
class RWMB_Range_Field extends RWMB_Number_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return sprintf(
			'<div class="rwmb-range-inner">
				%s
				<span class="rwmb-range-output">%s</span>
			</div>',
			parent::html( $meta, $field ),
			$meta
		);
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-range', RWMB_CSS_URL . 'range.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-range', RWMB_JS_URL . 'range.js', [], RWMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'max' => 10,
		] );
		$field = parent::normalize( $field );
		return $field;
	}

	/**
	 * Ensure number in range.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return int
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = (float) $new;
		$min = (float) $field['min'];
		$max = (float) $field['max'];

		if ( $new < $min ) {
			return $min;
		}
		if ( $new > $max ) {
			return $max;
		}
		return $new;
	}
}
