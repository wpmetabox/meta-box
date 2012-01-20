<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

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
			$std		 = isset( $field['disabled'] ) ? $field['disabled'] : false;
			$disabled	 = disabled( $std, true, false );

			$name		 = "name='{$field['field_name']}'";
			$id			 = " id='{$field['id']}'";
			$val		 = " value='{$meta}'";
			$size		 = isset( $field['size'] ) ? $field['size'] : '30';

			$html		.= "<input type='text' class='rwmb-text'{$name}{$id}{$val}{$disabled} size='{$size}' />";

			return $html;
		}
	}
}