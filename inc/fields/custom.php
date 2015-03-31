<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Text_Field' ) )
{
	class RWMB_Text_Field extends RWMB_Field
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
			if( empty( $field[ 'callback' ] ) )
				return '';

			if( is_callable( $field[ 'callback' ] ) )
			{
				$html = call_user_func_array( $field[ 'callback' ], array( $meta, $field ) );
			}
			else
			{
				$html = apply_filters( 'rwmb_custom_field_html', '', $meta, $field );
			}
			return $html;
		}
	}
}
