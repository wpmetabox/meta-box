<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Password_Field extends RWMB_Text_Field
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

		$field['attributes']['type'] = 'password';

		return $field;
	}
}
