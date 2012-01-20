<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') ) 
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Color_Field' ) ) 
{
	class RWMB_Color_Field 
	{
		/**
		 * Enqueue scripts and styles
		 * 
		 * @return void
		 */
		static function admin_print_styles() 
		{
			wp_enqueue_style( 'rwmb-color', RWMB_CSS_URL.'color.css', array( 'farbtastic' ), RWMB_VER );
			wp_enqueue_script( 'rwmb-color', RWMB_JS_URL.'color.js', array( 'farbtastic' ), RWMB_VER, true );
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
			if ( empty( $meta ) )
				$meta = '#';
			$name = "name='{$field['field_name']}'";

			$html = <<<HTML
<input class="rwmb-color" type="text" {$name} id="{$field['id']}" value="{$meta}" size="8" />
<a href="#" class="rwmb-color-select" rel="{$field['id']}">%s</a>
<div class="rwmb-color-picker" rel="{$field['id']}"></div>
HTML;
			$html = sprintf( $html, __( 'Select a color', RWMB_TEXTDOMAIN ) );

			return $html;
		}
	}
}