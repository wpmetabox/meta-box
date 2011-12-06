<?php

if ( ! class_exists( 'RWMB_Select_Field' ) ) 
{
	class RWMB_Select_Field 
	{
		/**
		 * Enqueue scripts and styles
		 * 
		 * @return	void
		 */
		static function admin_print_styles( ) 
		{
			wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', RWMB_VER );
		}

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
			if ( ! is_array( $meta ) )
				$meta = (array) $meta;

			$name	 = " name='{$field['id']}";
			$name	.= $field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'";
			$html	 = "<select class='rwmb-select'{$name} >";
			foreach ( $field['options'] as $key => $value ) 
			{
				$selected	 = selected( in_array( $key, $meta ), true, false );
				$html 		.= "<option value='{$key}'{$selected}>{$value}</option>";
			}
			$html	.= "</select>";

			return $html;
		}
	}
}