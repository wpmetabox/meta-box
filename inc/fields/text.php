<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Text_Field extends RWMB_Input_Field
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
			'size'        => 30,
			'maxlength'   => false,
			'pattern'     => false,
		) );

		$field['attributes'] = wp_parse_args( $field['attributes'], array(
			'size'        => $field['size'],
			'maxlength'   => $field['maxlength'],
			'pattern'     => $field['pattern'],
			'placeholder' => $field['placeholder'],
		) );

		$field['attributes']['type'] = 'text';

		return $field;
	}
}
