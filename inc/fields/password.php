<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Password_Field extends RWMB_Text_Field
{
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['type'] = 'password';
			
		return $attributes;
	}
}
