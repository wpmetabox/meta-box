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
	 * Register hook to sanitize field value.
	 */
	public function init() {
		add_filter( 'rwmb_sanitize', array( $this, 'sanitize' ), 10, 4 );
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
		$callback = $this->get_callback( $field );

		return is_callable( $callback ) ? call_user_func( $callback, $value, $field, $old_value, $object_id ) : $value;
	}

	/**
	 * Get sanitize callback for a field.
	 *
	 * @param  array $field Field settings.
	 * @return callable
	 */
	private function get_callback( $field ) {
		// User-defined callback.
		if ( is_callable( $field['sanitize_callback'] ) ) {
			return $field['sanitize_callback'];
		}

		$callbacks = array(
			'autocomplete'    => array( $this, 'sanitize_choice' ),
			'background'      => array( $this, 'sanitize_background' ),
			'button_group'    => array( $this, 'sanitize_choice' ),
			'checkbox'        => array( $this, 'sanitize_checkbox' ),
			'checkbox_list'   => array( $this, 'sanitize_choice' ),
			'color'           => array( $this, 'sanitize_color' ),
			'date'            => 'sanitize_text_field',
			'datetime'        => 'sanitize_text_field',
			'email'           => 'sanitize_email',
			'fieldset_text'   => array( $this, 'sanitize_text' ),
			'file_advanced'   => array( $this, 'sanitize_object' ),
			'file_input'      => 'esc_url_raw',
			'file_upload'     => array( $this, 'sanitize_object' ),
			'image_advanced'  => array( $this, 'sanitize_object' ),
			'image_select'    => array( $this, 'sanitize_choice' ),
			'image_upload'    => array( $this, 'sanitize_object' ),
			'number'          => array( $this, 'sanitize_number' ),
			'oembed'          => 'esc_url_raw',
			'post'            => array( $this, 'sanitize_object' ),
			'radio'           => array( $this, 'sanitize_choice' ),
			'range'           => array( $this, 'sanitize_number' ),
			'select'          => array( $this, 'sanitize_choice' ),
			'select_advanced' => array( $this, 'sanitize_choice' ),
			'single_image'    => 'absint',
			'switch'          => array( $this, 'sanitize_checkbox' ),
			'text'            => 'sanitize_text_field',
			'textarea'        => 'wp_kses_post',
			'time'            => 'sanitize_text_field',
			'url'             => 'esc_url_raw',
			'user'            => array( $this, 'sanitize_object' ),
			'video'           => array( $this, 'sanitize_object' ),
			'wysiwyg'         => 'wp_kses_post',
		);

		$type = $field['type'];

		return isset( $callbacks[ $type ] ) ? $callbacks[ $type ] : null;
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
	 * Sanitize numeric value.
	 *
	 * @param  int|float $value The number value.
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

	/**
	 * Sanitize value for a choice field.
	 *
	 * @param  string|array $value The submitted value.
	 * @param  array        $field The field settings.
	 * @return string|array
	 */
	private function sanitize_choice( $value, $field ) {
		$options = $field['options'];
		return is_array( $value ) ? array_intersect( $value, array_keys( $options ) ) : ( isset( $options[ $value ] ) ? $value : '' );
	}

	/**
	 * Sanitize value for an object & media field.
	 *
	 * @param  mixed $value The submitted value.
	 * @return int|array
	 */
	private function sanitize_object( $value ) {
		return is_array( $value ) ? array_map( 'absint', $value ) : absint( $value );
	}

	/**
	 * Sanitize background field.
	 *
	 * @param  array $value The submitted value.
	 * @return array
	 */
	private function sanitize_background( $value ) {
		$value = wp_parse_args(
			$value,
			array(
				'color'      => '',
				'image'      => '',
				'repeat'     => '',
				'attachment' => '',
				'position'   => '',
				'size'       => '',
			)
		);
		$value['color'] = $this->sanitize_color( $value['color'] );
		$value['image'] = esc_url_raw( $value['image'] );

		$value['repeat']     = in_array( $value['repeat'], array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y', 'inherit' ), true ) ? $value['repeat'] : '';
		$value['position']   = in_array( $value['repeat'], array( 'top left', 'top center', 'top right', 'center left', 'center center', 'center right', 'bottom left', 'bottom center', 'bottom right' ), true ) ? $value['position'] : '';
		$value['attachment'] = in_array( $value['repeat'], array( 'fixed', 'scroll', 'inherit' ), true ) ? $value['attachment'] : '';
		$value['size']       = in_array( $value['repeat'], array( 'inherit', 'cover', 'contain' ), true ) ? $value['attachment'] : '';

		return $value;
	}

	/**
	 * Sanitize value for a text field.
	 *
	 * @param  mixed $value The submitted value.
	 * @return int|array
	 */
	private function sanitize_text( $value ) {
		return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
	}
}
