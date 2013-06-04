<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Header_Field' ) )
{
	class RWMB_Header_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-header', RWMB_CSS_URL . 'header.css', array(), RWMB_VER );
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
			return sprintf(
				'<h4>%s</h4>'
				,!$field['std'] ? 'Header' : $field['std']
			);
		}

	}
}
