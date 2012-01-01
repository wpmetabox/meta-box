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
			$name  = " name='{$field['id']}'";
			$id    = " id='{$field['id']}'";
			$style = " style='{$field['style']}'";
			$html .= "<textarea class='rwmb-textarea large-text'{$name}{$id}{$style} cols='60' rows='10'>{$meta}</textarea>";

			return $html;
		}
	}
}