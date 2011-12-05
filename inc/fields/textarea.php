<?php

if ( ! class_exists( 'RWMB_Textarea_Field' ) ) 
{
	class RWMB_Textarea_Field 
	{
		/**
		 * Get field end HTML
		 * 
		 * @param	(unknown_type)	$html	| 
		 * @param	(unknown_type)	$meta	| 
		 * @param	(unknown_type)	$field	| 
		 * @return	(string)		$html	| 
		 */
		static function html( $html, $meta, $field ) 
		{
			$name	 = " name='{$field['id']}'";
			$id		 = " id='{$field['id']}'";
			$html	.= "<textarea class='rwmb-textarea large-text'{$name}{$id} cols='60' rows='10'>{$meta}</textarea>";

			return $html;
		}
	}
}