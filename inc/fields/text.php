<?php
/**
 * Text field class.
 */
class RWMB_Text_Field extends RWMB_Input_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );

		$field = wp_parse_args( $field, array(
			'size'        => 30,
			'maxlength'   => false,
			'pattern'     => false,
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
			'size'        => $field['size'],
			'maxlength'   => $field['maxlength'],
			'pattern'     => $field['pattern'],
			'placeholder' => $field['placeholder'],
		) );

		$attributes['type'] = 'text';

		return $attributes;
	}
}
