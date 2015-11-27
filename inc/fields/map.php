<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Map_Field' ) )
{
	class RWMB_Map_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			/**
			 * Allows developers load more libraries via a filter.
			 * @link https://developers.google.com/maps/documentation/javascript/libraries
			 */
			$google_maps_url = apply_filters( 'rwmb_google_maps_url', 'https://maps.google.com/maps/api/js?sensor=false' );
			wp_register_script( 'google-maps', esc_url_raw( $google_maps_url ), array(), '', true );
			wp_enqueue_style( 'rwmb-map', RWMB_CSS_URL . 'map.css' );
			wp_enqueue_script( 'rwmb-map', RWMB_JS_URL . 'map.js', array( 'jquery-ui-autocomplete', 'google-maps' ), RWMB_VER, true );
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$html = '<div class="rwmb-map-field">';

			$html .= sprintf(
				'<div class="rwmb-map-canvas" data-default-loc="%s"></div>
				<input type="hidden" name="%s" class="rwmb-map-coordinate" value="%s">',
				esc_attr( $field['std'] ),
				esc_attr( $field['field_name'] ),
				esc_attr( $meta )
			);

			if ( $address = $field['address_field'] )
			{
				$html .= sprintf(
					'<button class="button rwmb-map-goto-address-button" value="%s">%s</button>',
					is_array( $address ) ? implode( ',', $address ) : $address,
					__( 'Find Address', 'meta-box' )
				);
			}

			$html .= '</div>';

			return $html;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'std'           => '',
				'address_field' => '',
			) );

			return $field;
		}

		/**
		 * Get the field value
		 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
		 * of the field saved in the database, while this function returns more meaningful value of the field
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Not used for this field
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Array(latitude, longitude, zoom)
		 */
		static function get_value( $field, $args = array(), $post_id = null )
		{
			$value = parent::get_value( $field, $args, $post_id );
			list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );
			return compact( 'latitude', 'longitude', 'zoom' );
		}

		/**
		 * Output the field value
		 * Display Google maps
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = self::get_value( $field, $args, $post_id );
			if ( ! $value['latitude'] || ! $value['longitude'] )
			{
				return '';
			}
			if ( ! $value['zoom'] )
			{
				$value['zoom'] = 14;
			}

			/**
			 * Enqueue scripts
			 * Note: We still can enqueue script which outputs in the footer
			 */
			/**
			 * Allows developers load more libraries via a filter.
			 * @link https://developers.google.com/maps/documentation/javascript/libraries
			 */
			$google_maps_url = apply_filters( 'rwmb_google_maps_url', 'https://maps.google.com/maps/api/js?sensor=false' );
			wp_register_script( 'google-maps', esc_url_raw( $google_maps_url ), array(), '', true );
			wp_enqueue_script( 'rwmb-map-frontend', RWMB_JS_URL . 'map-frontend.js', array( 'google-maps' ), '', true );

			// Map parameters
			$args = wp_parse_args( $args, array(
				'latitude'     => $value['latitude'],
				'longitude'    => $value['longitude'],
				'width'        => '100%',
				'height'       => '480px',
				'marker'       => true, // Display marker?
				'marker_title' => '', // Marker title, when hover
				'info_window'  => '', // Content of info window (when click on marker). HTML allowed
				'js_options'   => array(),
			) );

			/**
			 * Google Maps options
			 * Option name is the same as specified in Google Maps documentation
			 * This array will be convert to Javascript Object and pass as map options
			 * @link https://developers.google.com/maps/documentation/javascript/reference
			 */
			$args['js_options'] = wp_parse_args( $args['js_options'], array(
				// Default to 'zoom' level set in admin, but can be overwritten
				'zoom'      => $value['zoom'],

				// Map type, see https://developers.google.com/maps/documentation/javascript/reference#MapTypeId
				'mapTypeId' => 'ROADMAP',
			) );

			$output = sprintf(
				'<div class="rwmb-map-canvas" data-map_options="%s" style="width:%s;height:%s"></div>',
				esc_attr( wp_json_encode( $args ) ),
				esc_attr( $args['width'] ),
				esc_attr( $args['height'] )
			);
			return $output;
		}
	}
}
