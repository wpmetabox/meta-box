<?php
defined( 'ABSPATH' ) || die;

/**
 * The number field which uses HTML <input type="number">.
 */
class RWMB_Number_Field extends RWMB_Input_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'step' => 1,
			'min'  => 0,
			'max'  => false,
		] );

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
		$attributes = wp_parse_args( $attributes, [
			'step' => $field['step'],
			'max'  => $field['max'],
			'min'  => $field['min'],
		] );
		return $attributes;
	}
}
