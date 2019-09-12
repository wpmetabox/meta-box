<?php
/**
 * The date picker field, which uses built-in jQueryUI date picker widget.
 *
 * @package Meta Box
 */

/**
 * Date field class.
 */
class RWMB_Date_Field extends RWMB_Datetime_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_register_scripts();
		wp_enqueue_style( 'rwmb-date' );
		wp_enqueue_script( 'rwmb-date' );
	}

	/**
	 * Returns a date() compatible format string from the JavaScript format.
	 *
	 * @link http://www.php.net/manual/en/function.date.php
	 * @param array $js_options JavaScript options.
	 *
	 * @return string
	 */
	public static function get_php_format( $js_options ) {
		return strtr( $js_options['dateFormat'], self::$date_formats );
	}
}
