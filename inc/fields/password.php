<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Password_Field' ) )
{
	class RWMB_Password_Field
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
			$val   = " value='{$meta}'";
			$name  = "name='{$field['field_name']}'";
			$id    = " id='{$field['id']}'";
			$html .= "<input type='password' class='rwmb-password'{$name}{$id}{$val} size='30' />";

			return $html;
		}
	}
}