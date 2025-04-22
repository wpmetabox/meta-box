<?php
defined( 'ABSPATH' ) || die;

/**
 * The Open Street Map field.
 */
class RWMB_OSM_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		self::enqueue_map_assets();

		wp_enqueue_style( 'rwmb-osm', RWMB_CSS_URL . 'osm.css', [ 'leaflet' ], RWMB_VER );
		wp_style_add_data( 'rwmb-osm', 'path', RWMB_CSS_DIR . 'osm.css' );
		wp_enqueue_script( 'rwmb-osm', RWMB_JS_URL . 'osm.js', [ 'jquery', 'jquery-ui-autocomplete', 'leaflet' ], RWMB_VER, true );

		RWMB_Helpers_Field::localize_script_once( 'rwmb-osm', 'RWMB_Osm', [ 
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
			'<div class="rwmb-osm-field" data-address-field="%s">',
			esc_attr( $address )
		);

		$attributes          = self::get_attributes( $field, $meta );
		$attributes['type']  = 'hidden';
		$attributes['value'] = $meta;

		$html .= sprintf(
			'<div class="rwmb-osm-canvas" data-default-loc="%s" data-region="%s" data-language="%s"></div>
			<input %s>',
			esc_attr( $field['std'] ),
			esc_attr( $field['region'] ),
			esc_attr( $field['language'] ),
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
				$location[]                          = compact( 'latitude', 'longitude', 'zoom' );
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
		return self::render_map( $value, $args );
	}

	/**
	 * Render a map in the frontend.
	 *
	 * @param string|array $location The "latitude,longitude[,zoom]" location.
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
		] );

		self::enqueue_map_assets();
		wp_enqueue_script( 'rwmb-osm-frontend', RWMB_JS_URL . 'osm-frontend.js', [ 'jquery', 'leaflet' ], RWMB_VER, true );
		wp_enqueue_style( 'rwmb-osm-frontend', RWMB_CSS_URL . 'osm-frontend.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-osm-frontend', 'path', RWMB_CSS_DIR . 'osm-frontend.css' );

		/*
		 * More Open Street Map options
		 * @link https://leafletjs.com/reference-1.5.0.html#map-option
		 */
		$args['js_options'] = wp_parse_args( $args['js_options'], [ 
			// Default to 'zoom' level set in admin, but can be overwritten.
			'zoom' => $args['zoom'],
		] );

		$output = sprintf(
			'<div class="rwmb-osm-canvas" data-osm_options="%s" style="width:%s;height:%s"></div>',
			esc_attr( wp_json_encode( $args ) ),
			esc_attr( $args['width'] ),
			esc_attr( $args['height'] )
		);
		return $output;
	}

	private static function enqueue_map_assets() {
		wp_enqueue_style( 'leaflet', RWMB_JS_URL . 'leaflet/leaflet.css', [], '1.9.4' );
		wp_style_add_data( 'leaflet', 'path', RWMB_JS_URL . 'leaflet/leaflet.css' );
		wp_enqueue_script( 'leaflet', RWMB_JS_URL . 'leaflet/leaflet.js', [], '1.9.4', true );
		wp_enqueue_style( 'leaflet-gesture-handling', RWMB_JS_URL . 'leaflet/leaflet-gesture-handling.min.css', [ 'leaflet' ], '1.2.2' );
		wp_style_add_data( 'leaflet-gesture-handling', 'path', RWMB_JS_URL . 'leaflet/leaflet-gesture-handling.min.css' );
		wp_enqueue_script( 'leaflet-gesture-handling', RWMB_JS_URL . 'leaflet/leaflet-gesture-handling.min.js', [ 'leaflet' ], '1.2.2' );
	}
}
