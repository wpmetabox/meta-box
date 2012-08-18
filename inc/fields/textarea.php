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
			$name = " name='{$field['field_name']}'";
			$id   = isset( $field['clone'] ) && $field['clone'] ? '' : " id='{$field['id']}'";
			$cols = " cols='{$field['cols']}'";
			$rows = " rows='{$field['rows']}'";
			
			$html .= "<textarea class='rwmb-textarea large-text'{$name}{$id}{$cols}{$rows}>{$meta}</textarea>";

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
			$field['cols'] = empty( $field['cols'] ) ? 60 : $field['cols'];
			$field['rows'] = empty( $field['rows'] ) ? 4  : $field['rows'];
			return $field;
		}
	}
}