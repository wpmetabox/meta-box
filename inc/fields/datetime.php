<?php
/**
 * The date and time picker field which allows users to select both date and time via jQueryUI datetime picker.
 *
 * @package Meta Box
 */

/**
 * Datetime field class.
 */
class RWMB_Datetime_Field extends RWMB_Text_Field {
	/**
	 * Translate date format from jQuery UI date picker to PHP date().
	 * It's used to store timestamp value of the field.
	 * Missing:  '!' => '', 'oo' => '', '@' => '', "''" => "'".
	 *
	 * @var array
	 */
	protected static $date_formats = array(
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
	);

	/**
	 * Translate time format from jQuery UI time picker to PHP date().
	 * It's used to store timestamp value of the field.
	 * Missing: 't' => '', T' => '', 'm' => '', 's' => ''.
	 *
	 * @var array
	 */
	protected static $time_formats = array(
		'H'  => 'G',
		'HH' => 'H',
		'h'  => 'g',
		'hh' => 'h',
		'mm' => 'i',
		'ss' => 's',
		'l'  => 'u',
		'tt' => 'a',
		'TT' => 'A',
	);

	/**
	 * Register scripts and styles.
	 */
	public static function admin_register_scripts() {
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_register_style( 'jquery-ui-core', "$url/jquery.ui.core.css", array(), '1.8.17' );
		wp_register_style( 'jquery-ui-theme', "$url/jquery.ui.theme.css", array(), '1.8.17' );
		wp_register_style( 'jquery-ui-datepicker', "$url/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
		wp_register_style( 'rwmb-date', RWMB_CSS_URL . 'date.css', array( 'jquery-ui-datepicker' ), '1.8.17' );

		wp_register_style( 'jquery-ui-slider', "$url/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
		wp_register_style( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.css", array( 'rwmb-date', 'jquery-ui-slider' ), '1.5.0' );

		$url = RWMB_JS_URL . 'jqueryui';
		wp_register_script( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.5.0', true );
		wp_register_script( 'jquery-ui-timepicker-i18n', "$url/jquery-ui-timepicker-addon-i18n.min.js", array( 'jquery-ui-timepicker' ), '1.5.0', true );

		wp_register_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', array( 'jquery-ui-datepicker', 'jquery-ui-timepicker-i18n', 'underscore' ), RWMB_VER, true );
		wp_register_script( 'rwmb-date', RWMB_JS_URL . 'date.js', array( 'jquery-ui-datepicker', 'underscore' ), RWMB_VER, true );
		wp_register_script( 'rwmb-time', RWMB_JS_URL . 'time.js', array( 'jquery-ui-timepicker-i18n' ), RWMB_VER, true );

		$handles      = array( 'datetime', 'time' );
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$data         = array(
			'locale'      => $locale,
			'localeShort' => $locale_short,
		);
		foreach ( $handles as $handle ) {
			RWMB_Helpers_Field::localize_script_once( "rwmb-$handle", 'RWMB_' . ucfirst( $handle ), $data );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		self::admin_register_scripts();
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
			$name    = $field['field_name'];
			$field   = wp_parse_args(
				array(
					'field_name' => $name . '[formatted]',
				),
				$field
			);
			$output .= sprintf(
				'<input type="hidden" name="%s" class="rwmb-datetime-timestamp" value="%s">',
				esc_attr( $name . '[timestamp]' ),
				isset( $meta['timestamp'] ) ? intval( $meta['timestamp'] ) : ''
			);
			$meta    = isset( $meta['formatted'] ) ? $meta['formatted'] : '';
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
			return $new['timestamp'];
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
			return RWMB_Helpers_Array::map( $meta, __CLASS__ . '::from_timestamp', $field );
		}

		if ( $field['save_format'] && $meta ) {
			return RWMB_Helpers_Array::map( $meta, __CLASS__ . '::from_save_format', $field );
		}

		return $meta;
	}

	/**
	 * Format meta value if set 'timestamp'.
	 *
	 * @param  string $meta  The meta value.
	 * @param  array  $field Field parameters.
	 * @return array
	 */
	public static function from_timestamp( $meta, $field ) {
		return array(
			'timestamp' => $meta ? $meta : null,
			'formatted' => $meta ? date( $field['php_format'], intval( $meta ) ) : '',
		);
	}

	/**
	 * Transform meta value from save format to the JS format.
	 *
	 * @param  string $meta  The meta value.
	 * @param  array  $field Field parameters.
	 * @return array
	 */
	public static function from_save_format( $meta, $field ) {
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
		$field = wp_parse_args(
			$field,
			array(
				'timestamp'   => false,
				'inline'      => false,
				'js_options'  => array(),
				'save_format' => '',
			)
		);

		// Deprecate 'format', but keep it for backward compatible.
		// Use 'js_options' instead.
		$field['js_options'] = wp_parse_args(
			$field['js_options'],
			array(
				'timeFormat'      => 'HH:mm',
				'separator'       => ' ',
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'showButtonPanel' => true,
			)
		);

		if ( $field['inline'] ) {
			$field['js_options'] = wp_parse_args(
				$field['js_options'],
				array(
					'altFieldTimeOnly' => false,
				)
			);
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
		$attributes         = wp_parse_args(
			$attributes,
			array(
				'data-options' => wp_json_encode( $field['js_options'] ),
			)
		);
		$attributes['type'] = 'text';

		return $attributes;
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format.
	 *
	 * @link http://www.php.net/manual/en/function.date.php
	 * @param array $js_options JavaScript options.
	 *
	 * @return string
	 */
	protected static function get_php_format( $js_options ) {
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
			$value = array(
				'timestamp' => strtotime( $value ),
				'formatted' => $value,
			);
		}
		return empty( $args['format'] ) ? $value['formatted'] : date( $args['format'], $value['timestamp'] );
	}
}
