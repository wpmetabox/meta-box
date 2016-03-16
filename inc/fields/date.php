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
}
