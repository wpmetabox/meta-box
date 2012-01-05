<?php

if ( ! class_exists( 'RWMB_Hidden_Field' ) ) 
{
	class RWMB_Hidden_Field 
	{
		/**
		 * Show begin HTML markup for fields
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $html, $meta, $field )
		{
			return '<div class="hidden">';
		}

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
			$name  = " name='{$field['id']}'";
			$id    = " id='{$field['id']}'";
			$html .= "<input type='hidden' class='rwmb-text rwmb-hidden'{$name}{$id}{$val} size='30' />";

			return $html;
		}
	}
}