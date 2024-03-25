<?php
defined( 'ABSPATH' ) || die;

/**
 * The time picker field.
 */
class RWMB_Time_Field extends RWMB_Datetime_Field {
	public static function admin_enqueue_scripts() {
		parent::register_assets();
		wp_enqueue_style( 'jquery-ui-timepicker' );
		wp_enqueue_script( 'rwmb-time' );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field                             = parent::normalize( $field );
		$field['js_options']['timeFormat'] = empty( $field['format'] ) ? $field['js_options']['timeFormat'] : $field['format'];
		return $field;
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format.
	 * @link http://www.php.net/manual/en/function.date.php
	 */
	protected static function get_php_format( array $js_options ): string {
		return strtr( $js_options['timeFormat'], self::$time_formats );
	}
}