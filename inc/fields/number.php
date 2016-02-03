<?php
/**
 * Number field class.
 */
class RWMB_Number_Field extends RWMB_Input_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );

		$field = wp_parse_args( $field, array(
			'step' => 1,
			'min'  => 0,
			'max'  => false,
		) );

		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'step' => $field['step'],
			'max'  => $field['max'],
			'min'  => $field['min'],
		) );
		$attributes['type'] = 'number';

		return $attributes;
	}
}
