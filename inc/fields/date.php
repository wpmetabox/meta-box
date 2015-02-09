<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Date_Field' ) )
{
	class RWMB_Date_Field extends RWMB_Field
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
			wp_enqueue_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );

			// Load localized scripts
			$locale     = str_replace( '_', '-', get_locale() );
			$file_paths = array( 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js' );
			// Also check alternate i18n filename (e.g. jquery.ui.datepicker-de.js instead of jquery.ui.datepicker-de-DE.js)
			if ( strlen( $locale ) > 2 )
				$file_paths[] = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . substr( $locale, 0, 2 ) . '.js';
			$deps = array( 'jquery-ui-datepicker' );
			foreach ( $file_paths as $file_path )
			{
				if ( file_exists( RWMB_DIR . 'js/' . $file_path ) )
				{
					wp_register_script( 'jquery-ui-datepicker-i18n', RWMB_JS_URL . $file_path, $deps, '1.8.17', true );
					$deps[] = 'jquery-ui-datepicker-i18n';
					break;
				}
			}

			wp_enqueue_script( 'rwmb-date', RWMB_JS_URL . 'date.js', $deps, RWMB_VER, true );
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
				'<input type="text" class="rwmb-date" name="%s" value="%s" id="%s" size="%s" data-options="%s" />',
				$field['field_name'],
				$meta,
				isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
				$field['size'],
				esc_attr( json_encode( $field['js_options'] ) )
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
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'showButtonPanel' => true,
			) );

			return $field;
		}
	}
}
