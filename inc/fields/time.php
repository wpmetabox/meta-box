<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Time_Field' ) )
{
	class RWMB_Time_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return    void
		 */
		static function admin_enqueue_scripts()
		{
			$url = RWMB_CSS_URL . 'jqueryui';
			wp_register_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
			wp_register_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );
			wp_register_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_register_style( 'jquery-ui-slider', "{$url}/jquery.ui.slider.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );
			wp_enqueue_style( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.min.css", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.5.0' );

			$url = RWMB_JS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.min.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.5.0', true );

			/**
			 * Localization
			 * Use 1 minified JS file which contains all languages for simpilicity (in version < 4.4.2 we use separated JS files).
			 * The language is set in Javascript
			 *
			 * Note: we use full locale (de-DE) and fallback to short locale (de)
			 */
			$locale = str_replace( '_', '-', get_locale() );
			$locale_short = substr( $locale, 0, 2 );
			wp_register_script( 'jquery-ui-timepicker-i18n', "{$url}/jquery-ui-timepicker-addon-i18n.min.js", array( 'jquery-ui-timepicker' ), '1.5.0', true );

			wp_enqueue_script( 'rwmb-time', RWMB_JS_URL . 'time.js', array( 'jquery-ui-timepicker-i18n' ), RWMB_VER, true );
			wp_localize_script( 'rwmb-time', 'RWMB_Timepicker', array(
				'locale'      => $locale,
				'localeShort' => $locale_short,
			) );
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
				'<input type="text" class="rwmb-time" name="%s" value="%s" id="%s" size="%s" data-options="%s">',
				$field['field_name'],
				$meta,
				isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
				$field['size'],
				esc_attr( wp_json_encode( $field['js_options'] ) )
			);
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
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'showButtonPanel' => true,
				'timeFormat'      => empty( $field['format'] ) ? 'HH:mm' : $field['format'],
			) );

			return $field;
		}
	}
}
