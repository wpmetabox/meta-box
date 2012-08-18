<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Radio_Field' ) )
{
	class RWMB_Radio_Field
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
			$html = '';
			$name = "name='{$field['field_name']}'";

			foreach ( $field['options'] as $key => $label )
			{
				$id      = strstr( $field['id'], '[]' ) ? str_replace( '[]', "-{$key}[]", $field['id'] ) : $field['id'];
				$id      = " id='{$id}'";
				$value   = " value='{$key}'";
				$checked = checked( $meta, $key, false );

				$html .= "<label><input type='radio' class='rwmb-radio'{$name}{$id}{$value}{$checked} /> {$label}</label> ";
			}

			return $html;
		}
	}
}