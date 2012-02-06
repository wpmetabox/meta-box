<?php
// Prevent loading this file directly - Busted!
if ( ! class_exists( 'WP' ) ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Checkbox_Field' ) ) 
{
	class RWMB_Checkbox_Field 
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
			$checked = checked( ! empty( $meta ), true, false );
			$name    = "name='{$field['field_name']}'";
			$id      = " id='{$field['id']}'";
			$html    = "<input type='checkbox' class='rwmb-checkbox'{$name}{$id}{$checked} />";

			return $html;
		}

		/**
		 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string
		 * This prevents using default value once the checkbox has been unchecked
		 *
		 * @link https://github.com/rilwis/meta-box/issues/6
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return int
		 */
		static function value( $new, $old, $post_id, $field ) 
		{
			return empty( $new ) ? 0 : 1;
		}
	}
}