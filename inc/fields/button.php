<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Button_Field' ) )
{
	class RWMB_Button_Field extends RWMB_Field
	{
		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			return sprintf(
				'<a href="#" id="%s" class="button hide-if-no-js">%s</a>',
				$field['id'],
				$field['std']
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
			$field['std'] = $field['std'] ? $field['std'] : __( 'Click me', 'meta-box' );

			return $field;
		}
	}
}
