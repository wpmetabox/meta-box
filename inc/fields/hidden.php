<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Hidden_Field extends RWMB_Input_Field
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

		$field['attributes'] = array(
			'name' => $field['field_name'],
			'id'   => $field['clone'] ? false : $field['id'],
		);

		return $field;
	}
}
