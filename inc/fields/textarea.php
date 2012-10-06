<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Textarea_Field' ) )
{
	class RWMB_Textarea_Field
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
				'<textarea class="rwmb-textarea large-text" name="%s" id="%s" cols="%s" rows="%s">%s</textarea>',
				$field['field_name'],
				$field['id'],
				$field['cols'],
				$field['rows'],
				$meta
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
				'cols' => 60,
				'rows' => 3,
			) );
			return $field;
		}
	}
}