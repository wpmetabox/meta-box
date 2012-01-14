<?php

if ( ! class_exists( 'RWMB_Slider_Field' ) )
{
	class RWMB_Slider_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_print_styles()
		{
			wp_register_style( 'jquery-ui-core', RWMB_CSS_URL . 'libs/jquery.ui.core.css', array(), '1.8.16' );
			wp_register_style( 'jquery-ui-theme', RWMB_CSS_URL . 'libs/jquery.ui.theme.css', array(), '1.8.16' );

			wp_enqueue_script( 'jquery-ui-slider', '', array( 'jquery-ui-core' ), '1.8.16', true );
			wp_enqueue_script( 'rwmb-slider', RWMB_JS_URL . 'slider.js', array( 'jquery-ui-slider' ), RWMB_VER, true );
		}

		/**
		 * Get div HTML
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			$id	     = " id='{$field['id']}'";
			$name	 = "name='{$field['field_name']}'";
			$val     = " value='{$meta}'";
			$for     = " for='{$field['id']}'";
			$format	 = " rel='{$field['format']}'";
			$html   .= "
				<div class='clearfix'>
					<div class='rwmb-slider'{$format}{$id}></div>
					<input type='hidden'{$name}{$val} />
				</div>";

			return $html;
		}
	}
}