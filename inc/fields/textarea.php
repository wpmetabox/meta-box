<?php
/**
 * The textarea field.
 *
 * @package Meta Box
 */

/**
 * Textarea field class.
 */
class RWMB_Textarea_Field extends RWMB_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, $meta );
		return sprintf(
			'<textarea %s>%s</textarea>',
			self::render_attributes( $attributes ),
			$meta
		);
	}

	/**
	 * Escape meta for field output.
	 *
	 * @param mixed $meta Meta value.
	 * @return mixed
	 */
	public static function esc_meta( $meta ) {
		return is_array( $meta ) ? array_map( 'esc_textarea', $meta ) : esc_textarea( $meta );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'cols'      => 60,
			'rows'      => 3,
			'maxlength' => false,
			'wrap'      => false,
			'readonly'  => false,
		) );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'cols'        => $field['cols'],
			'rows'        => $field['rows'],
			'maxlength'   => $field['maxlength'],
			'wrap'        => $field['wrap'],
			'readonly'    => $field['readonly'],
			'placeholder' => $field['placeholder'],
		) );
		$attributes['class'] .= ' large-text';

		return $attributes;
	}
}
