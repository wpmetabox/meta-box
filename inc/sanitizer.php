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
		// Built-in WordPress callbacks.
		'date'       => 'sanitize_text_field',
		'datetime'   => 'sanitize_text_field',
		'email'      => 'sanitize_email',
		'file_input' => 'esc_url_raw',
		'oembed'     => 'esc_url_raw',
		'post'       => 'absint',
		'text'       => 'sanitize_text_field',
		'textarea'   => 'wp_kses_post',
		'time'       => 'sanitize_text_field',
		'url'        => 'esc_url_raw',
		'user'       => 'absint',
		'wysiwyg'    => 'wp_kses_post',
	);

	/**
	 * Register hook to sanitize field value.
	 */
	public function init() {
		add_filter( 'rwmb_value', array( $this, 'sanitize' ), 10, 4 );
	}

	/**
	 * Sanitize a field value.
	 *
	 * @param mixed $value     The submitted new value.
	 * @param array $field     The field settings.
	 * @param mixed $old_value The old field value in the database.
	 * @param int   $object_id The object ID.
	 */
	public function sanitize( $value, $field, $old_value, $object_id ) {
		$type = $field['type'];

		// User-defined callback.
		$callback = $field['sanitize_callback'];

		// Built-in WordPress callback.
		if ( ! is_callable( $callback ) && isset( $this->callbacks[ $type ] ) ) {
			$callback = $this->callbacks[ $type ];
		}

		// Custom callback.
		if ( ! is_callable( $callback ) && method_exists( $this, "sanitize_$type" ) ) {
			$callback = array( $this, "sanitize_$type" );
		}

		return is_callable( $callback ) ? call_user_func( $callback, $value, $old_value, $field, $object_id ) : $value;
	}

	/**
	 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string.
	 * This prevents using default value once the checkbox has been unchecked.
	 *
	 * @link https://github.com/rilwis/meta-box/issues/6
	 * @param string $value Checkbox value.
	 * @return int
	 */
	private function sanitize_checkbox( $value ) {
		return (int) ! empty( $value );
	}

	/**
	 * Set the value of switch to 1 or 0 instead of 'checked' and empty string.
	 * This prevents using default value once the switch has been unchecked.
	 *
	 * @param string $value Switch value.
	 * @return int
	 */
	private function sanitize_switch( $value ) {
		return (int) ! empty( $value );
	}

	/**
	 * Sanitize numeric value.
	 *
	 * @param string $value The number value.
	 * @return int|float
	 */
	private function sanitize_number( $value ) {
		return is_numeric( $value ) ? $value : 0;
	}

	/**
	 * Sanitize color value.
	 *
	 * @param string $value The color value.
	 * @return string
	 */
	private function sanitize_color( $value ) {
		if ( false === strpos( $value, 'rgba' ) ) {
			return sanitize_hex_color( $value );
		}

		// rgba value.
		$red   = '';
		$green = '';
		$blue  = '';
		$alpha = '';
		sscanf( $value, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
	}
}
