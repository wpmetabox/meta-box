<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

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

		$field['attributes'] = wp_parse_args( $field['attributes'], array(
			'step' => $field['step'],
			'max'  => $field['max'],
			'min'  => $field['min'],
		) );

		$field['attributes']['type'] = 'number';

		return $field;
	}
}
