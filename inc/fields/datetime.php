<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Datetime_Field' ) )
{
	class RWMB_Datetime_Field extends RWMB_Field
	{
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
			wp_register_style( 'jquery-ui-slider', "{$url}/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_enqueue_style( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.css", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7' );

			wp_register_script( 'jquery-ui-timepicker', RWMB_JS_URL . 'jqueryui/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );

			// Load localized scripts
			$locale     = str_replace( '_', '-', get_locale() );
			$date_paths = array( 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js' );
			$time_paths = array( 'jqueryui/timepicker-i18n/jquery-ui-timepicker-' . $locale . '.js' );
			if ( strlen( $locale ) > 2 )
			{
				// Also check alternate i18n filenames
				// (e.g. jquery.ui.datepicker-de.js instead of jquery.ui.datepicker-de-DE.js)
				$date_paths[] = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . substr( $locale, 0, 2 ) . '.js';
				$time_paths[] = 'jqueryui/timepicker-i18n/jquery-ui-timepicker-' . substr( $locale, 0, 2 ) . '.js';
			}
			$deps = array( 'jquery-ui-datepicker', 'jquery-ui-timepicker' );
			foreach ( $date_paths as $date_path )
			{
				if ( file_exists( RWMB_DIR . 'js/' . $date_path ) )
				{
					wp_register_script( 'jquery-ui-datepicker-i18n', RWMB_JS_URL . $date_path, array( 'jquery-ui-datepicker' ), '1.8.17', true );
					$deps[] = 'jquery-ui-datepicker-i18n';
					break;
				}
			}
			foreach ( $time_paths as $time_path )
			{
				if ( file_exists( RWMB_DIR . 'js/' . $time_path ) )
				{
					wp_register_script( 'jquery-ui-timepicker-i18n', RWMB_JS_URL . $time_path, array( 'jquery-ui-timepicker' ), '1.8.17', true );
					$deps[] = 'jquery-ui-timepicker-i18n';
					break;
				}
			}

			wp_enqueue_script( 'rwmb-datetime', RWMB_JS_URL . 'datetime.js', $deps, RWMB_VER, true );
			wp_localize_script( 'rwmb-datetime', 'RWMB_Datetimepicker', array( 'lang' => $locale ) );
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
			return sprintf(
				'<input type="text" class="rwmb-datetime" name="%s" value="%s" id="%s" size="%s" data-options="%s">',
				$field['field_name'],
				isset( $field['timestamp'] ) && $field['timestamp'] ? date( self::translate_format( $field ), $meta ) : $meta,
				isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
				$field['size'],
				esc_attr( json_encode( $field['js_options'] ) )
			);
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
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'       => 30,
				'js_options' => array(),
				'timestamp'  => false,
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'timeFormat'      => 'hh:mm',
				'showButtonPanel' => true,
				'separator'       => ' ',
			) );

			return $field;
		}

		// Missing: 't' => '', T' => '', 'm' => '', 's' => ''
		static $time_format_translation = array(
			'H'  => 'H', 'HH' => 'H', 'h' => 'H', 'hh' => 'H',
			'mm' => 'i', 'ss' => 's', 'l' => 'u', 'tt' => 'a', 'TT' => 'A',
		);

		// Missing:  'o' => '', '!' => '', 'oo' => '', '@' => '', "''" => "'"
		static $date_format_translation = array(
			'd' => 'j', 'dd' => 'd', 'oo' => 'z', 'D' => 'D', 'DD' => 'l',
			'm' => 'n', 'mm' => 'm', 'M' => 'M', 'MM' => 'F', 'y' => 'y', 'yy' => 'Y',
		);

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
