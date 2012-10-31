<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'RWMB_Map_Field' ) )
{
	class RWMB_Map_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_script( 'googlemap', 'http://maps.google.com/maps/api/js?sensor=false', array(), '', true );
			wp_enqueue_script( 'rwmb-map', RWMB_JS_URL . 'map.js', array( 'jquery', 'googlemap' ), RWMB_VER, true );
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
			$address = isset( $field['address_field'] ) ? $field['address_field'] : false;

			$html = sprintf(
				'<div class="rwmb-map-canvas" style="%s"></div>
				<input type="hidden" name="%s" id="rwmb-map-coordinate" value="%s" />',
				isset( $field['style'] ) ? $field['style'] : '',
				$field['field_name'],
				$meta
			);

			if ( $address )
			{
				$html .= sprintf(
					'<button class="button" type="button" id="rwmb-map-goto-address-button" value="%s" onclick="geocodeAddress(this.value);">%s</button>',
					is_array( $address ) ? implode( ',', $address ) : $address,
					__( 'Find Address', 'rwmb' )
				);
			}
			return $html;
		}
	}
}