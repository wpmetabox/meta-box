<?php

if ( ! class_exists( 'RWMB_Date_Field' ) ) 
{
	class RWMB_Date_Field 
	{
		/**
		 * Enqueue scripts and styles
		 * 
		 * @return void
		 */
		static function admin_print_styles() 
		{
			wp_register_style( 'jquery-ui-core', RWMB_CSS_URL.'libs/jquery.ui.core.css', array(), '1.8.16' );
			wp_register_style( 'jquery-ui-theme', RWMB_CSS_URL.'libs/jquery.ui.theme.css', array(), '1.8.16' );
			wp_enqueue_style( 'jquery-ui-datepicker', RWMB_CSS_URL.'libs/jquery.ui.datepicker.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.16' );

			wp_register_script( 'jquery-ui-datepicker', RWMB_JS_URL.'libs/jquery.ui.datepicker.min.js', array( 'jquery-ui-core' ), '1.8.16', true );
			wp_enqueue_script( 'rwmb-date', RWMB_JS_URL.'date.js', array( 'jquery-ui-datepicker' ), RWMB_VER, true );
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
			$name = "name='{$field['field_name']}'";
			$id     = " id='{$field['id']}'";
			$format = " rel='{$field['format']}'";
			$val    = " value='{$meta}'";
			$html   = "<input type='text' class='rwmb-date'{$name}{$id}{$format}{$val} size='30' />";

			return $html;
		}
	}
}