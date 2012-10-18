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
			wp_enqueue_script( 'googlemap', 'http://maps.google.com/maps/api/js?sensor=false', array(), false, true );
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
			$std = isset( $field['disabled'] ) ? $field['disabled'] : false;
			$disabled = disabled( $std, true, false );

			$name = " name='{$field['field_name']}'";
			$id = " id='{$field['id']}'";
			$value = " value='{$meta}'";

			$address = isset( $field['address_field'] ) ? $field['address_field'] : false;

			$html .= "<div class='rwmb-map-canvas'" . ( isset( $field['style'] ) ? " style='" . $field['style'] . "'" : "" ) . "></div>";
			$html .= "<input type='hidden'{$name} id='rwmb-map-coordinate' {$value} />\n";
			if ( $address )
			{
				$html .= "<button type='button' name='rwmb-map-goto-address-button' id='rwmb-map-goto-address-button' value='" . ( is_array( $address ) ? implode( ",", $address ) : $address ) . "' onclick='geocodeAddress(this.value);'>Find Address</button>\n";
			}
			return $html;
		}
	}
}