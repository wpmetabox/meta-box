<?php
/**
 * Sanitize field value before saving.
 *
 * @package Meta Box
 */

/**
 * Sanitize class.
 */
class RWMB_Sanitizer {
	/**
	 * Built-in callbacks for some specific types.
	 *
	 * @var array
	 */
	private $callbacks = array(
		'email'      => 'sanitize_email',
		'file_input' => 'esc_url_raw',
		'oembed'     => 'esc_url_raw',
		'url'        => 'esc_url_raw',
	);

	/**
	 * Register hook to sanitize field value.
	 */
	public function init() {
		add_filter( 'rwmb_value', array( $this, 'run_sanitize_callback' ), 10, 4 );

		// Built-in callback.
		foreach ( $this->callbacks as $type => $callback ) {
			add_filter( "rwmb_{$type}_value", $callback );
		}

		// Custom callback.
		$methods = array_diff( get_class_methods( __CLASS__ ), array( 'init', 'run_sanitize_callback' ) );
		foreach ( $methods as $method ) {
			$type = substr( $method, 9 );
			add_filter( "rwmb_{$type}_value", array( $this, $method ) );
		}
	}

	/**
	 * Run `sanitize_callback` for each field if it's defined.
	 *
	 * @param mixed $value     The submitted new value.
	 * @param array $field     The field settings.
	 * @param mixed $old_value The old field value in the database.
	 * @param int   $object_id The object ID.
	 */
	public function run_sanitize_callback( $value, $field, $old_value, $object_id ) {
		if ( ! is_callable( $field['sanitize_callback'] ) ) {
			return $value;
		}
		return call_user_func( $field['sanitize_callback'], $value, $old_value, $field, $object_id );
	}

	/**
	 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string.
	 * This prevents using default value once the checkbox has been unchecked.
	 *
	 * @link https://github.com/rilwis/meta-box/issues/6
	 * @param string $value Checkbox value.
	 * @return int
	 */
	public function sanitize_checkbox( $value ) {
		return (int) ! empty( $value );
	}

	/**
	 * Set the value of switch to 1 or 0 instead of 'checked' and empty string.
	 * This prevents using default value once the switch has been unchecked.
	 *
	 * @param string $value Switch value.
	 * @return int
	 */
	public function sanitize_switch( $value ) {
		return (int) ! empty( $value );
	}
}
