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
			$name  = " name='{$field['field_name']}'";
			$id    = isset( $field['clone'] ) && $field['clone'] ? '' : " id='{$field['id']}'";
			$value = " value='{$meta}'";
			$size  = " size='{$field['size']}'";

			$html .= "<input type='text' class='rwmb-text'{$name}{$id}{$value}{$size} />";

			return $html;
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
			$field['size'] = empty( $field['size'] ) ? 30 : $field['size'];
			return $field;
		}
	}
}