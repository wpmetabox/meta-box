<?php
/**
 * Date field class.
 */
class RWMB_Date_Field extends RWMB_Datetime_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts()
	{
		parent::admin_register_scripts();
		wp_enqueue_style( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'rwmb-date' );
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
		return strtr( $field['js_options']['dateFormat'], self::$date_formats );
	}
}
