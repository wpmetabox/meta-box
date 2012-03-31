<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Slider_Field' ) )
{
	class RWMB_Slider_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$url = RWMB_CSS_URL . 'jqueryui';
			wp_enqueue_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
			wp_enqueue_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );

			$url = RWMB_JS_URL . 'jqueryui';
			wp_enqueue_script( 'jquery-ui-slider', "{$url}/jquery.ui.slider.min.js", array( 'jquery-ui-core' ), '1.8.17', true );
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