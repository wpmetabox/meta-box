<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

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
			$std		 = isset( $field['disabled'] ) ? $field['disabled'] : false;
			$disabled	 = disabled( $std, true, false );

			$cols	 = isset( $field['cols'] ) ? $field['cols'] : "60";
			$rows	 = isset( $field['rows'] ) ? $field['rows'] : "10";
			$name	 = "name='{$field['field_name']}'";
			$id		 = " id='{$field['id']}'";
			$html	.= "<textarea class='rwmb-textarea large-text'{$name}{$id} cols='{$cols}' rows='{$rows}'{$disabled}>{$meta}</textarea>";

			return $html;
		}
	}
}