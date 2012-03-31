<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Datetime_Field' ) )
{
	class RWMB_Datetime_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return	void
		 */
		static function admin_enqueue_scripts( )
		{
			$url = RWMB_CSS_URL . 'jqueryui';
			wp_register_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array( ), '1.8.17' );
			wp_register_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array( ), '1.8.17' );
			wp_register_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_register_style( 'jquery-ui-slider', "{$url}/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_enqueue_style( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.css", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7' );

			$url = RWMB_JS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.min.js", array( 'jquery-ui-core' ), '1.8.17', true );
			wp_register_script( 'jquery-ui-slider', "{$url}/jquery.ui.slider.min.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse' ), '1.8.17', true );
			wp_register_script( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', array( 'jquery-ui-timepicker' ), RWMB_VER, true );
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
			$name  = " name='{$field['field_name']}'";
			$id    = isset( $field['clone'] ) && $field['clone'] ? '' : " id='{$field['id']}'";
			$rel   = " rel='{$field['format']}'";
			$val   = " value='{$meta}'";
			$html .= "<input type='text' class='rwmb-datetime' size='30'{$name}{$id}{$rel}{$val} />";

			return $html;
		}
	}
}