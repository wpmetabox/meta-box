<?php

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
			$val   = " value='{$meta}'";
			$name = "name='{$field['field_name']}'";
			$id    = " id='{$field['id']}'";
			$html .= "<input type='text' class='rwmb-text'{$name}{$id}{$val} size='30' />";

			return $html;
		}
	}
}