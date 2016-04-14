<?php
/**
 * Hidden field class.
 */
class RWMB_Hidden_Field extends RWMB_Input_Field
{
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
		$attributes['type'] = 'hidden';

		return $attributes;
	}
}
