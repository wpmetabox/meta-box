<?php

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
			$cols	 = isset( $field['cols'] ) ? $field['cols'] : "60";
			$rows	 = isset( $field['rows'] ) ? $field['rows'] : "10";
			$name	 = "name='{$field['field_name']}'";
			$id		 = " id='{$field['id']}'";
			$html	.= "<textarea class='rwmb-textarea large-text'{$name}{$id} cols='{$cols}' rows='{$rows}'>{$meta}</textarea>";

			return $html;
		}
	}
}