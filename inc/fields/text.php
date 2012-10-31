<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Text_Field' ) )
{
	class RWMB_Text_Field
	{
		/**
		 * Get field HTML
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			return sprintf(
				'<input type="text" class="rwmb-text" name="%s" id="%s" value="%s" size="%s" />',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['size']
			);
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size' => 30,
			) );
			return $field;
		}
	}
}