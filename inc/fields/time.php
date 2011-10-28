<?php

if ( !class_exists( 'RWMB_Time_Field' ) ) {

	class RWMB_Time_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_register_style( 'jquery-ui-core', RWMB_CSS_URL . 'libs/jquery.ui.core.css', array( ), '1.8.16' );
			wp_register_style( 'jquery-ui-theme', RWMB_CSS_URL . 'libs/jquery.ui.theme.css', array( ), '1.8.16' );
			wp_register_style( 'jquery-ui-datepicker', RWMB_CSS_URL . 'libs/jquery.ui.datepicker.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.16' );
			wp_register_style( 'jquery-ui-slider', RWMB_CSS_URL . 'libs/jquery.ui.slider.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.16' );
			wp_enqueue_style( 'jquery-ui-timepicker', RWMB_CSS_URL . 'libs/jquery-ui-timepicker-addon.css', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7' );

			wp_register_script( 'jquery-ui-datepicker', RWMB_JS_URL . 'libs/jquery.ui.datepicker.min.js', array( 'jquery-ui-core' ), '1.8.16', true );
			wp_register_script( 'jquery-ui-slider', RWMB_JS_URL . 'libs/jquery.ui.slider.min.js', array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse' ), '1.8.16', true );
			wp_register_script( 'jquery-ui-timepicker', RWMB_JS_URL . 'libs/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_script( 'rwmb-time', RWMB_JS_URL . 'time.js', array( 'jquery-ui-timepicker' ), RWMB_VER, true );
		}

		/**
		 * Get field end HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			return "<input type='text' class='rwmb-time' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='$meta' size='30' />";
		}
	}
}