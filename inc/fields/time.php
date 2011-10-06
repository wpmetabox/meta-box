<?php

if ( !class_exists( 'RW_Meta_Box_Time_Field' ) ) {

	class RW_Meta_Box_Time_Field {

		/**
		 * Enqueue scripts and styles for time field
		 */
		static function admin_print_styles( ) {
			wp_register_style( 'jquery-ui-core', RW_META_BOX_CSS . 'libs/jquery.ui.core.css', array( ), '1.8.16' );
			wp_register_style( 'jquery-ui-theme', RW_META_BOX_CSS . 'libs/jquery.ui.theme.css', array( ), '1.8.16' );
			wp_register_style( 'jquery-ui-datepicker', RW_META_BOX_CSS . 'libs/jquery.ui.datepicker.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.16' );
			wp_register_style( 'jquery-ui-slider', RW_META_BOX_CSS . 'libs/jquery.ui.slider.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.16' );
			wp_enqueue_style( 'jquery-ui-timepicker', RW_META_BOX_CSS . 'libs/jquery-ui-timepicker-addon.css', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7' );

			wp_register_script( 'jquery-ui-datepicker', RW_META_BOX_JS . 'libs/jquery.ui.datepicker.min.js', array( 'jquery-ui-core' ), '1.8.16', true );
			wp_register_script( 'jquery-ui-slider', RW_META_BOX_JS . 'libs/jquery.ui.slider.min.js', array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse' ), '1.8.16', true );
			wp_register_script( 'jquery-ui-timepicker', RW_META_BOX_JS . 'libs/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_script( 'rw-meta-box-time', RW_META_BOX_JS . 'time.js', array( 'jquery-ui-timepicker' ), RW_META_BOX_VER, true );
		}

		/**
		 * Show HTML markup for time field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<input type='text' class='rw-time' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='$meta' size='30' />";
		}
	}
}