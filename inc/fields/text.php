<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "input" field is loaded
require_once RWMB_FIELDS_DIR . 'input.php';

if ( ! class_exists( 'RWMB_Text_Field' ) )
{
	class RWMB_Text_Field extends RWMB_Input_Field
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

			$field = wp_parse_args( $field, array(
				'size'        => 30,
				'maxlength'   => false,
				'pattern'     => false,
				'placeholder' => '',
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
}
