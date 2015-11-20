<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "input" field is loaded
require_once RWMB_FIELDS_DIR . 'input.php';

if ( ! class_exists( 'RWMB_Hidden_Field' ) )
{
	class RWMB_Hidden_Field extends RWMB_Input_Field
	{
		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = parent::normalize_field( $field );

			$field['attributes'] = array(
				'name' => $field['field_name'],
				'id'   => $field['clone'] ? false : $field['id'],
			);

			return $field;
		}
	}
}
