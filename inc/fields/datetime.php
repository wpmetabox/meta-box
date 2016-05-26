<?php

/**
 * Datetime field class.
 */
class RWMB_Datetime_Field extends RWMB_Text_Field
{
	/**
	 * Translate date format from jQuery UI date picker to PHP date()
	 * It's used to store timestamp value of the field
	 * Missing:  '!' => '', 'oo' => '', '@' => '', "''" => "'"
	 * @var array
	 */
	protected static $date_formats = array(
		'd' => 'j', 'dd' => 'd', 'oo' => 'z', 'D' => 'D', 'DD' => 'l',
		'm' => 'n', 'mm' => 'm', 'M' => 'M', 'MM' => 'F', 'y' => 'y', 'yy' => 'Y', 'o' => 'z',
	);

	/**
	 * Translate time format from jQuery UI time picker to PHP date()
	 * It's used to store timestamp value of the field
	 * Missing: 't' => '', T' => '', 'm' => '', 's' => ''
	 * @var array
	 */
	protected static $time_formats = array(
		'H'  => 'G', 'HH' => 'H', 'h' => 'g', 'hh' => 'h',
		'mm' => 'i', 'ss' => 's', 'l' => 'u', 'tt' => 'a', 'TT' => 'A',
	);

	/**
	 * Register scripts and styles
	 */
	public static function admin_register_scripts()
	{
		$url = RWMB_CSS_URL . 'jqueryui';
		wp_register_style( 'jquery-ui-core', "$url/jquery.ui.core.css", array(), '1.8.17' );
		wp_register_style( 'jquery-ui-theme', "$url/jquery.ui.theme.css", array(), '1.8.17' );
		wp_register_style( 'wp-datepicker', RWMB_CSS_URL . 'datepicker.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
		wp_register_style( 'jquery-ui-datepicker', "$url/jquery.ui.datepicker.css", array( 'wp-datepicker' ), '1.8.17' );
		wp_register_style( 'jquery-ui-slider', "$url/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
		wp_register_style( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.css", array( 'jquery-ui-datepicker', 'jquery-ui-slider', 'wp-datepicker' ), '1.5.0' );

		$url = RWMB_JS_URL . 'jqueryui';
		wp_register_script( 'jquery-ui-datepicker-i18n', "$url/jquery-ui-i18n.min.js", array( 'jquery-ui-datepicker' ), '1.11.4', true );
		wp_register_script( 'jquery-ui-timepicker', "$url/jquery-ui-timepicker-addon.min.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.5.0', true );
		wp_register_script( 'jquery-ui-timepicker-i18n', "$url/jquery-ui-timepicker-addon-i18n.min.js", array( 'jquery-ui-timepicker' ), '1.5.0', true );

		wp_register_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', array( 'jquery-ui-datepicker-i18n', 'jquery-ui-timepicker-i18n' ), RWMB_VER, true );
		wp_register_script( 'rwmb-date', RWMB_JS_URL . 'date.js', array( 'jquery-ui-datepicker-i18n', 'jquery-ui-timepicker-i18n' ), RWMB_VER, true );
		wp_register_script( 'rwmb-time', RWMB_JS_URL . 'time.js', array( 'jquery-ui-timepicker-i18n' ), RWMB_VER, true );

		/**
		 * Add data to scripts. Prevent loading localized string twice.
		 * @link https://github.com/rilwis/meta-box/issues/850
		 */
		$wp_scripts   = wp_scripts();
		$handles      = array( 'datetime', 'date', 'time' );
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$data         = array(
			'locale'      => $locale,
			'localeShort' => $locale_short,
		);
		foreach ( $handles as $handle )
		{
			if ( ! $wp_scripts->get_data( "rwmb-$handle", 'data' ) )
			{
				wp_localize_script( "rwmb-$handle", 'RWMB_' . ucfirst( $handle ), $data );
			}
		}
	}

	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		self::admin_register_scripts();
		wp_enqueue_style( 'jquery-ui-timepicker' );
		wp_enqueue_script( 'rwmb-datetime' );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	public static function html( $meta, $field )
	{
		$output = '';

		if ( $field['timestamp'] )
		{
			$name  = $field['field_name'];
			$field = wp_parse_args( array( 'field_name' => $name . '[formatted]' ), $field );
			$output .= sprintf(
				'<input type="hidden" name="%s" class="rwmb-datetime-timestamp" value="%s">',
				esc_attr( $name . '[timestamp]' ),
				isset( $meta['timestamp'] ) ? intval( $meta['timestamp'] ) : ''
			);
			$meta = isset( $meta['formatted'] ) ? $meta['formatted'] : '';
		}

		$output .= parent::html( $meta, $field );

		if ( $field['inline'] )
		{
			$output .= '<div class="rwmb-datetime-inline"></div>';
		}

		return $output;
	}

	/**
	 * Calculates the timestamp from the datetime string and returns it
	 * if $field['timestamp'] is set or the datetime string if not
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return string|int
	 */
	public static function value( $new, $old, $post_id, $field )
	{
		if ( $field['timestamp'] )
		{
			$new = self::prepare_value( $new, $field );
		}
		return $new;
	}

	/**
	 * Prepare value before saving if set 'timestamp'
	 * @param array|string $value The submitted value
	 * @param array        $field Field parameter
	 * @return array
	 */
	protected static function prepare_value( $value, $field )
	{
		if ( $field['clone'] )
		{
			return array_map( __METHOD__, $value );
		}
		return isset( $value['timestamp'] ) ? $value['timestamp'] : null;
	}

	/**
	 * Get meta value
	 *
	 * @param int   $post_id
	 * @param bool  $saved
	 * @param array $field
	 *
	 * @return mixed
	 */
	public static function meta( $post_id, $saved, $field )
	{
		$meta = parent::meta( $post_id, $saved, $field );
		if ( $field['timestamp'] )
		{
			$meta = self::prepare_meta( $meta, $field );
		}
		return $meta;
	}

	/**
	 * Format meta value if set 'timestamp'
	 * @param array|string $meta  The meta value
	 * @param array        $field Field parameter
	 * @return array
	 */
	protected static function prepare_meta( $meta, $field )
	{
		if ( is_array( $meta ) )
		{
			return array_map( __METHOD__, $meta );
		}
		return array(
			'timestamp' => $meta ? $meta : null,
			'formatted' => $meta ? date( self::call( 'translate_format', $field ), intval( $meta ) ) : '',
		);
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	public static function normalize( $field )
	{
		$field = wp_parse_args( $field, array(
			'timestamp'  => false,
			'inline'     => false,
			'js_options' => array(),
		) );

		// Deprecate 'format', but keep it for backward compatible
		// Use 'js_options' instead
		$field['js_options'] = wp_parse_args( $field['js_options'], array(
			'timeFormat'      => 'HH:mm',
			'separator'       => ' ',
			'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
			'showButtonPanel' => true,
		) );

		if ( $field['inline'] )
		{
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'altFieldTimeOnly' => false,
			) );
		}

		$field = RWMB_Text_Field::normalize( $field );

		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'data-options' => wp_json_encode( $field['js_options'] ),
		) );
		$attributes['type'] = 'text';

		return $attributes;
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format
	 *
	 * @link http://www.php.net/manual/en/function.date.php
	 * @param array $field
	 *
	 * @return string
	 */
	public static function translate_format( $field )
	{
		return strtr( $field['js_options']['dateFormat'], self::$date_formats )
		. $field['js_options']['separator']
		. strtr( $field['js_options']['timeFormat'], self::$time_formats );
	}
}
