<?php
defined( 'ABSPATH' ) || die;

/**
 * The image select field which behaves similar to the radio field but uses images as options.
 */
class RWMB_Image_Select_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-image-select', RWMB_CSS_URL . 'image-select.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-image-select', 'path', RWMB_CSS_DIR . 'image-select.css' );
		wp_enqueue_script( 'rwmb-image-select', RWMB_JS_URL . 'image-select.js', [ 'jquery' ], RWMB_VER, true );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html    = [];
		$meta    = (array) $meta;
		foreach ( $field['options'] as $value => $image ) {
			$attributes = self::get_attributes( $field, $value );
			$html[]     = sprintf(
				'<label class="rwmb-image-select"><img src="%s"><input %s%s></label>',
				$image,
				self::render_attributes( $attributes ),
				checked( in_array( $value, $meta ), true, false )
			);
		}

		return implode( ' ', $html );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field                = parent::normalize( $field );
		$field['options']     = $field['options'] ?? [];
		$field['field_name'] .= $field['multiple'] ? '[]' : '';

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes          = parent::get_attributes( $field, $value );
		$attributes['id']    = false;
		$attributes['type']  = $field['multiple'] ? 'checkbox' : 'radio';
		$attributes['value'] = $value;

		return $attributes;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return $value ? sprintf( '<img src="%s">', esc_url( $field['options'][ $value ] ) ) : '';
	}
}
