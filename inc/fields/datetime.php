<?php
use MetaBox\Support\Arr;

/**
 * The date and time picker field which allows users to select both date and time via jQueryUI datetime picker.
 */
class RWMB_Datetime_Field extends RWMB_Input_Field {
	/**
	 * Translate date format from jQuery UI date picker to PHP date().
	 * It's used to store timestamp value of the field.
	 * Missing:  '!' => '', 'oo' => '', '@' => '', "''" => "'".
	 *
	 * @var array
	 */
	protected static $date_formats = [
		'd'  => 'j',
		'dd' => 'd',
		'oo' => 'z',
		'D'  => 'D',
		'DD' => 'l',
		'm'  => 'n',
		'mm' => 'm',
		'M'  => 'M',
		'MM' => 'F',
		'y'  => 'y',
		'yy' => 'Y',
		'o'  => 'z',
	];

	/**
	 * Translate time format from jQuery UI time picker to PHP date().
	 * It's used to store timestamp value of the field.
	 * Missing: 't' => '', T' => '', 'm' => '', 's' => ''.
	 *
	 * @var array
	 */
	protected static $time_formats = [
		'H'  => 'G',
		'HH' => 'H',
		'h'  => 'g',
		'hh' => 'h',
		'mm' => 'i',
		'ss' => 's',
		'l'  => 'u',
		'tt' => 'a',
		'TT' => 'A',
	];

	public static function register_assets() {
		// jQueryUI base theme: https://github.com/jquery/jquery-ui/tree/1.13.2/themes/base
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_register_style( 'jquery-ui-core', "$url/core.css", [], '1.13.2' );
		wp_register_style( 'jquery-ui-theme', "$url/theme.css", [], '1.13.2' );
		wp_register_style( 'jquery-ui-datepicker', "$url/datepicker.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );
		wp_register_style( 'jquery-ui-slider', "$url/slider.css", [ 'jquery-ui-core', 'jquery-ui-theme' ], '1.13.2' );

		// jQueryUI timepicker addon: https://github.com/trentrichardson/jQuery-Timepicker-Addon
		wp_register_style( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.css", [ 'rwmb-date', 'jquery-ui-slider' ], '1.6.3' );

		wp_register_style( 'rwmb-date', RWMB_CSS_URL . 'date.css', [ 'jquery-ui-datepicker' ], RWMB_VER );

		// Scripts.
		$url = RWMB_JS_URL . 'jqueryui';
		wp_register_script( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.js", [ 'jquery-ui-datepicker', 'jquery-ui-slider' ], '1.6.3', true );
		wp_register_script( 'jquery-ui-timepicker-slider', "$url/jquery-ui-sliderAccess.js", [ 'jquery-ui-datepicker', 'jquery-ui-slider' ], '0.3', true );
		wp_register_script( 'jquery-ui-timepicker-i18n', "$url/jquery-ui-timepicker-addon-i18n.min.js", [ 'jquery-ui-timepicker' ], '1.6.3', true );

		wp_register_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', [ 'jquery-ui-datepicker', 'jquery-ui-timepicker-i18n', 'underscore', 'jquery-ui-button', 'jquery-ui-timepicker-slider', 'rwmb' ], RWMB_VER, true );
		wp_register_script( 'rwmb-date', RWMB_JS_URL . 'date.js', [ 'jquery-ui-datepicker', 'underscore', 'rwmb' ], RWMB_VER, true );
		wp_register_script( 'rwmb-time', RWMB_JS_URL . 'time.js', [ 'jquery-ui-timepicker-i18n', 'jquery-ui-button', 'jquery-ui-timepicker-slider', 'rwmb' ], RWMB_VER, true );

		$handles      = [ 'datetime', 'time' ];
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$data         = [
			'locale'      => $locale,
			'localeShort' => $locale_short,
		];
		foreach ( $handles as $handle ) {
			RWMB_Helpers_Field::localize_script_once( "rwmb-$handle", 'RWMB_' . ucfirst( $handle ), $data );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		self::register_assets();
		wp_enqueue_style( 'jquery-ui-timepicker' );
		wp_enqueue_script( 'rwmb-datetime' );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  The field meta value.
	 * @param array $field The field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$output = '';

		if ( $field['timestamp'] ) {
			$name      = $field['field_name'];
			$field     = wp_parse_args( [ 'field_name' => $name . '[formatted]' ], $field );
			$timestamp = $meta['timestamp'] ?? 0;
			$output   .= sprintf(
				'<input type="hidden" name="%s" class="rwmb-datetime-timestamp" value="%s">',
				esc_attr( $name . '[timestamp]' ),
				(int) $timestamp
			);

			$meta = $meta['formatted'] ?? '';
		}

		$output .= parent::html( $meta, $field );

		if ( $field['inline'] ) {
			$output .= '<div class="rwmb-datetime-inline"></div>';
		}

		return $output;
	}

	/**
	 * Calculates the timestamp from the datetime string and returns it if $field['timestamp'] is set or the datetime string if not.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return string|int
	 */
	public static function value( $new, $old, $post_id, $field ) {
		if ( $field['timestamp'] ) {
			if ( is_array( $new ) ) {
				return $new['timestamp'];
			}
			if ( ! is_numeric( $new ) ) {
				return strtotime( $new );
			}
			return $new;
		}

		if ( $field['save_format'] ) {
			$date = DateTime::createFromFormat( $field['php_format'], $new );
			$new  = false === $date ? $new : $date->format( $field['save_format'] );
		}

		return $new;
	}

	/**
	 * Get meta value.
	 *
	 * @param int   $post_id The post ID.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param array $field   The field parameters.
	 *
	 * @return mixed
	 */
	public static function meta( $post_id, $saved, $field ) {
		$meta = parent::meta( $post_id, $saved, $field );

		if ( $field['timestamp'] ) {
			return Arr::map( $meta, __CLASS__ . '::from_timestamp', $field );
		}

		if ( $field['save_format'] && $meta ) {
			return Arr::map( $meta, __CLASS__ . '::from_save_format', $field );
		}

		return $meta;
	}

	/**
	 * Format meta value if set 'timestamp'.
	 */
	public static function from_timestamp( $meta, array $field ) : array {
		return [
			'timestamp' => $meta ?: null,
			'formatted' => $meta ? gmdate( $field['php_format'], intval( $meta ) ) : '',
		];
	}

	/**
	 * Transform meta value from save format to the JS format.
	 */
	public static function from_save_format( $meta, array $field ) : string {
		$date = DateTime::createFromFormat( $field['save_format'], $meta );
		return false === $date ? $meta : $date->format( $field['php_format'] );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field The field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'timestamp'    => false,
			'inline'       => false,
			'js_options'   => [],
			'save_format'  => '',
			'autocomplete' => 'off',
		] );

		// Deprecate 'format', but keep it for backward compatible.
		// Use 'js_options' instead.
		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'timeFormat'       => 'HH:mm',
			'separator'        => ' ',
			'dateFormat'       => $field['format'] ?? 'yy-mm-dd',
			'showButtonPanel'  => true,
			'changeYear'       => true,
			'yearRange'        => '-100:+100',
			'changeMonth'      => true,
			'oneLine'          => true,
			'controlType'      => 'select', // select or slider
			'addSliderAccess'  => true,
			'sliderAccessArgs' => [
				'touchonly' => true, // To show sliderAccess only on touch devices
			],
		] );

		if ( $field['inline'] ) {
			$field['js_options'] = wp_parse_args( $field['js_options'], [ 'altFieldTimeOnly' => false ] );
		}

		$field['php_format'] = static::get_php_format( $field['js_options'] );

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes         = parent::get_attributes( $field, $value );
		$attributes         = wp_parse_args( $attributes, [ 'data-options' => wp_json_encode( $field['js_options'] ) ] );
		$attributes['type'] = 'text';

		return $attributes;
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format.
	 * @link http://www.php.net/manual/en/function.date.php
	 */
	protected static function get_php_format( array $js_options ) : string {
		return strtr( $js_options['dateFormat'], self::$date_formats )
		. $js_options['separator']
		. strtr( $js_options['timeFormat'], self::$time_formats );
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( $field['timestamp'] ) {
			$value = self::from_timestamp( $value, $field );
		} else {
			$value = [
				'timestamp' => strtotime( $value ),
				'formatted' => $value,
			];
		}
		return empty( $args['format'] ) ? $value['formatted'] : gmdate( $args['format'], $value['timestamp'] );
	}
}
