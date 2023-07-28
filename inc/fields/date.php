<?php
defined( 'ABSPATH' ) || die;

/**
 * The date picker field, which uses built-in jQueryUI date picker widget.
 */
class RWMB_Date_Field extends RWMB_Datetime_Field {
	public static function admin_enqueue_scripts() {
		parent::register_assets();
		wp_enqueue_style( 'rwmb-date' );
		wp_enqueue_script( 'rwmb-date' );
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format.
	 * @link http://www.php.net/manual/en/function.date.php
	 */
	protected static function get_php_format( array $js_options ) : string {
		return strtr( $js_options['dateFormat'], self::$date_formats );
	}
}
