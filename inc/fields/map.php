<?php
defined( 'ABSPATH' ) || die;

/**
 * The Google Maps field.
 */
class RWMB_Map_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-map', RWMB_CSS_URL . 'map.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-map', 'path', RWMB_CSS_DIR . 'map.css' );

		$args            = func_get_args();
		$field           = $args[0];
		$google_maps_url = add_query_arg( [
			'key'       => $field['api_key'],
			'language'  => $field['language'],
			'libraries' => 'places',
		], 'https://maps.google.com/maps/api/js' );

		/**
		 * Allows developers load more libraries via a filter.
		 * @link https://developers.google.com/maps/documentation/javascript/libraries
		 */
		$google_maps_url = apply_filters( 'rwmb_google_maps_url', $google_maps_url );

		wp_register_script( 'google-maps', esc_url_raw( $google_maps_url ), [], RWMB_VER, true );
		wp_enqueue_script( 'rwmb-map', RWMB_JS_URL . 'map.js', [ 'jquery-ui-autocomplete', 'google-maps' ], RWMB_VER, true );
		RWMB_Helpers_Field::localize_script_once( 'rwmb-map', 'RWMB_Map', [
			'no_results_string' => __( 'No results found', 'meta-box' ),
		] );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$address = is_array( $field['address_field'] ) ? implode( ',', $field['address_field'] ) : $field['address_field'];
		$html    = sprintf(
			'<div class="rwmb-map-field" data-address-field="%s">',
			esc_attr( $address )
		);

		$attributes          = self::get_attributes( $field, $meta );
		$attributes['type']  = 'hidden';
		$attributes['value'] = $meta;

		$html .= sprintf(
			'<div class="rwmb-map-canvas" data-default-loc="%s" data-region="%s"></div>
			<input %s>',
			esc_attr( $field['std'] ),
			esc_attr( $field['region'] ),
			self::render_attributes( $attributes )
		);

		$html .= '</div>';

		return $html;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'std'           => '',
			'address_field' => '',
			'language'      => '',
			'region'        => '',

			// Default API key, required by Google Maps since June 2016.
			// Users should overwrite this key with their own key.
			'api_key'       => 'AIzaSyC1mUh87SGFyf133tpZQJa-s96p0tgnraQ',
		] );

		return $field;
	}

	/**
	 * Get the field value.
	 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
	 * of the field saved in the database, while this function returns more meaningful value of the field.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Not used for this field.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Array(latitude, longitude, zoom)
	 */
	public static function get_value( $field, $args = [], $post_id = null ) {
		$value = parent::get_value( $field, $args, $post_id );

		if ( is_array( $value ) ) {
			$location = [];
			foreach ( $value as $clone ) {
				list( $latitude, $longitude, $zoom ) = explode( ',', $clone . ',,' );
				$location[]                            = compact( 'latitude', 'longitude', 'zoom' );
			}
			return $location;
		}

		list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );
		return compact( 'latitude', 'longitude', 'zoom' );
	}

	/**
	 * Format value before render map
	 * @param mixed $field
	 * @param mixed $value
	 * @param mixed $args
	 * @param mixed $post_id
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ): string {
		$args = wp_parse_args( $args, [
			'api_key' => $field['api_key'] ?? '',
		] );
		return self::render_map( $value, $args );
	}

	/**
	 * Render a map in the frontend.
	 *
	 * @param string $location The "latitude,longitude[,zoom]" location.
	 * @param array  $args     Additional arguments for the map.
	 *
	 * @return string
	 */
	public static function render_map( $location, $args = [] ) {
        // For compatibility with previous version, or within groups.
		if ( is_string( $location ) ) {
			list( $latitude, $longitude, $zoom ) = explode( ',', $location . ',,' );
		} else {
			extract( $location );
		}

        if ( ! $latitude || ! $longitude ) {
            return '';
        }

		$args = wp_parse_args( $args, [
			'latitude'     => $latitude,
			'longitude'    => $longitude,
			'width'        => '100%',
			'height'       => '480px',
			'marker'       => true, // Display marker?
			'marker_title' => '', // Marker title, when hover.
			'info_window'  => '', // Content of info window (when click on marker). HTML allowed.
			'js_options'   => [],
			'zoom'         => $zoom,

			// Default API key, required by Google Maps since June 2016.
			// Users should overwrite this key with their own key.
			'api_key'      => 'AIzaSyC1mUh87SGFyf133tpZQJa-s96p0tgnraQ',
		] );

		$google_maps_url = add_query_arg( 'key', $args['api_key'], 'https://maps.google.com/maps/api/js' );

		/*
		 * Allows developers load more libraries via a filter.
		 * @link https://developers.google.com/maps/documentation/javascript/libraries
		 */
		$google_maps_url = apply_filters( 'rwmb_google_maps_url', $google_maps_url );
		wp_register_script( 'google-maps', esc_url_raw( $google_maps_url ), [], RWMB_VER, true );
		wp_enqueue_script( 'rwmb-map-frontend', RWMB_JS_URL . 'map-frontend.js', [ 'google-maps', 'jquery' ], RWMB_VER, true );

		/*
		 * Google Maps options.
		 * Option name is the same as specified in Google Maps documentation.
		 * This array will be convert to Javascript Object and pass as map options.
		 * @link https://developers.google.com/maps/documentation/javascript/reference
		 */
		$args['js_options'] = wp_parse_args( $args['js_options'], [
			// Default to 'zoom' level set in admin, but can be overwritten.
			'zoom'           => $args['zoom'],

			// Map type, see https://developers.google.com/maps/documentation/javascript/reference#MapTypeId.
			'mapTypeId'      => 'ROADMAP',

			// Open Info Window
			'openInfoWindow' => false,
		] );

		$output = sprintf(
			'<div class="rwmb-map-canvas" data-map_options="%s" style="width:%s;height:%s"></div>',
			esc_attr( wp_json_encode( $args ) ),
			esc_attr( $args['width'] ),
			esc_attr( $args['height'] )
		);
		return $output;
	}
}
