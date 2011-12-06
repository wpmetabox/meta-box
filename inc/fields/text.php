<?php

if ( ! class_exists( 'RWMB_Text_Field' ) ) 
{
	class RWMB_Text_Field 
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
			$val	 = " value='{$meta}'";
			$name	 = " name='{$field['id']}'";
			$id		 = " id='{$field['id']}'";
			$html	.= "<input type='text' class='rwmb-text'{$name}{$id}{$val} size='30' />";

			return $html;
		}
	}
}