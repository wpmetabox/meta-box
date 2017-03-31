<?php
/**
 * The checkbox field.
 *
 * @package Meta Box
 */

/**
 * Checkbox field class.
 */
class RWMB_Checkbox_Field extends RWMB_Input_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, 1 );
		$output     = sprintf(
			'<input %s %s>',
			self::render_attributes( $attributes ),
			checked( ! empty( $meta ), 1, false )
		);
		if ( $field['desc'] ) {
			$output = "<label id='{$field['id']}_description' class='description'>$output {$field['desc']}</label>";
		}
		return $output;
	}

	/**
	 * Do not show field description.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function input_description( $field ) {
		return '';
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value The value.
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		return $value ? __( 'Yes', 'meta-box' ) : __( 'No', 'meta-box' );
	}
}
