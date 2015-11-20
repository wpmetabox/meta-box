<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once RWMB_FIELDS_DIR . 'date.php';

if ( ! class_exists( 'RWMB_Datetime_Field' ) )
{
	class RWMB_Datetime_Field extends RWMB_Input_Field
	{
		/**
		 * Translate date format from jQuery UI datepicker to PHP date()
		 * It's used to store timestamp value of the field
		 * Missing:  'o' => '', '!' => '', 'oo' => '', '@' => '', "''" => "'"
		 * @var array
		 */
		static $date_format_translation = array(
			'd' => 'j', 'dd' => 'd', 'oo' => 'z', 'D' => 'D', 'DD' => 'l',
			'm' => 'n', 'mm' => 'm', 'M' => 'M', 'MM' => 'F', 'y' => 'y', 'yy' => 'Y',
		);

		/**
		 * Translate date format from jQuery UI datepicker to PHP date()
		 * It's used to store timestamp value of the field
		 * Missing: 't' => '', T' => '', 'm' => '', 's' => ''
		 * @var array
		 */
		static $time_format_translation = array(
			'H'  => 'G', 'HH' => 'H', 'h' => 'g', 'hh' => 'h',
			'mm' => 'i', 'ss' => 's', 'l' => 'u', 'tt' => 'a', 'TT' => 'A',
		);

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$url = RWMB_CSS_URL . 'jqueryui';
			wp_register_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
			wp_register_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );
			wp_register_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_register_style( 'wp-datepicker', RWMB_CSS_URL . 'datepicker.css', array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_register_style( 'jquery-ui-slider', "{$url}/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_enqueue_style( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.min.css", array( 'jquery-ui-datepicker', 'jquery-ui-slider', 'wp-datepicker' ), '1.5.0' );

			$url = RWMB_JS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.min.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.5.0', true );

			/**
			 * Localization
			 * Use 1 minified JS file for timepicker which contains all languages for simpilicity (in version < 4.4.2 we use separated JS files).
			 * The language is set in Javascript
			 *
			 * Note: we use full locale (de-DE) and fallback to short locale (de)
			 */
			$locale       = str_replace( '_', '-', get_locale() );
			$locale_short = substr( $locale, 0, 2 );

			wp_register_script( 'jquery-ui-timepicker-i18n', "{$url}/jquery-ui-timepicker-addon-i18n.min.js", array( 'jquery-ui-timepicker' ), '1.5.0', true );

			$date_paths = array( 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js' );
			if ( strlen( $locale ) > 2 )
			{
				// Also check alternate i18n filenames
				// (e.g. jquery.ui.datepicker-de.js instead of jquery.ui.datepicker-de-DE.js)
				$date_paths[] = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . substr( $locale, 0, 2 ) . '.js';
			}
			$deps = array( 'jquery-ui-timepicker-i18n' );
			foreach ( $date_paths as $date_path )
			{
				if ( file_exists( RWMB_DIR . 'js/' . $date_path ) )
				{
					wp_register_script( 'jquery-ui-datepicker-i18n', RWMB_JS_URL . $date_path, array( 'jquery-ui-datepicker' ), '1.8.17', true );
					$deps[] = 'jquery-ui-datepicker-i18n';
					break;
				}
			}

			wp_enqueue_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', $deps, RWMB_VER, true );
			wp_localize_script( 'rwmb-datetime', 'RWMB_Datetimepicker', array(
				'locale'      => $locale,
				'localeShort' => $locale_short,
			) );
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
		static function value( $new, $old, $post_id, $field )
		{
			if ( ! $field['timestamp'] )
				return $new;

			$d = DateTime::createFromFormat( self::translate_format( $field ), $new );

			return $d ? $d->getTimestamp() : 0;
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
		static function meta( $post_id, $saved, $field )
		{
			$meta = parent::meta( $post_id, $saved, $field );
			if ( is_array( $meta ) )
			{
				foreach ( $meta as $key => $value )
				{
					$meta[$key] = $field['timestamp'] && $value ? date( self::translate_format( $field ), intval( $value ) ) : $value;
				}
			}
			else
			{
				$meta = $field['timestamp'] && $meta ? date( self::translate_format( $field ), intval( $meta ) ) : $meta;
			}
			return $meta;
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
				'timestamp'  => false,
				'js_options' => array()
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'timeFormat' => 'HH:mm',
				'separator'  => ' ',
			) );

			$field = RWMB_Date_Field::normalize_field( $field );

			return $field;
		}

		/**
		 * Returns a date() compatible format string from the JavaScript format
		 *
		 * @see http://www.php.net/manual/en/function.date.php
		 *
		 * @param array $field
		 *
		 * @return string
		 */
		static function translate_format( $field )
		{
			return strtr( $field['js_options']['dateFormat'], self::$date_format_translation )
			. $field['js_options']['separator']
			. strtr( $field['js_options']['timeFormat'], self::$time_format_translation );
		}
	}
}
