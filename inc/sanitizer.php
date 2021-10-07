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
	public function sanitize( $value, $field, $old_value = null, $object_id = null ) {
		// Allow developers to bypass the sanitization.
		if ( 'none' === $field['sanitize_callback'] ) {
			return $value;
		}

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
			'autocomplete'      => array( $this, 'sanitize_choice' ),
			'background'        => array( $this, 'sanitize_background' ),
			'button_group'      => array( $this, 'sanitize_choice' ),
			'checkbox'          => array( $this, 'sanitize_checkbox' ),
			'checkbox_list'     => array( $this, 'sanitize_choice' ),
			'color'             => array( $this, 'sanitize_color' ),
			'date'              => array( $this, 'sanitize_datetime' ),
			'datetime'          => array( $this, 'sanitize_datetime' ),
			'email'             => 'sanitize_email',
			'fieldset_text'     => array( $this, 'sanitize_text' ),
			'file'              => array( $this, 'sanitize_file' ),
			'file_advanced'     => array( $this, 'sanitize_object' ),
			'file_input'        => array( $this, 'sanitize_url' ),
			'file_upload'       => array( $this, 'sanitize_object' ),
			'hidden'            => 'sanitize_text_field',
			'image'             => array( $this, 'sanitize_file' ),
			'image_advanced'    => array( $this, 'sanitize_object' ),
			'image_select'      => array( $this, 'sanitize_choice' ),
			'image_upload'      => array( $this, 'sanitize_object' ),
			'key_value'         => array( $this, 'sanitize_text' ),
			'map'               => array( $this, 'sanitize_map' ),
			'number'            => array( $this, 'sanitize_number' ),
			'oembed'            => array( $this, 'sanitize_url' ),
			'osm'               => array( $this, 'sanitize_map' ),
			'password'          => 'sanitize_text_field',
			'post'              => array( $this, 'sanitize_object' ),
			'radio'             => array( $this, 'sanitize_choice' ),
			'range'             => array( $this, 'sanitize_number' ),
			'select'            => array( $this, 'sanitize_choice' ),
			'select_advanced'   => array( $this, 'sanitize_choice' ),
			'sidebar'           => array( $this, 'sanitize_text' ),
			'single_image'      => 'absint',
			'slider'            => array( $this, 'sanitize_slider' ),
			'switch'            => array( $this, 'sanitize_checkbox' ),
			'taxonomy'          => array( $this, 'sanitize_object' ),
			'taxonomy_advanced' => array( $this, 'sanitize_taxonomy_advanced' ),
			'text'              => 'sanitize_text_field',
			'text_list'         => array( $this, 'sanitize_text' ),
			'textarea'          => 'wp_kses_post',
			'time'              => 'sanitize_text_field',
			'url'               => array( $this, 'sanitize_url' ),
			'user'              => array( $this, 'sanitize_object' ),
			'video'             => array( $this, 'sanitize_object' ),
			'wysiwyg'           => 'wp_kses_post',
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
	 * @param  string $value The number value.
	 * @return string
	 */
	private function sanitize_number( $value ) {
		return is_numeric( $value ) ? $value : '';
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
		$options = RWMB_Choice_Field::transform_options( $field['options'] );
		$options = wp_list_pluck( $options, 'value' );
		$value = wp_unslash( $value );
		return is_array( $value ) ? array_intersect( $value, $options ) : ( in_array( $value, $options ) ? $value : '' );
	}

	/**
	 * Sanitize object & media field.
	 *
	 * @param  int|array $value The submitted value.
	 * @return int|array
	 */
	private function sanitize_object( $value ) {
		return is_array( $value ) ? array_filter( array_map( 'absint', $value ) ) : ( $value ? absint( $value ) : '' );
	}

	/**
	 * Sanitize background field.
	 *
	 * @param  array $value The submitted value.
	 * @return array
	 */
	private function sanitize_background( $value ) {
		$value          = wp_parse_args(
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
		$value['position']   = in_array( $value['position'], array( 'top left', 'top center', 'top right', 'center left', 'center center', 'center right', 'bottom left', 'bottom center', 'bottom right' ), true ) ? $value['position'] : '';
		$value['attachment'] = in_array( $value['attachment'], array( 'fixed', 'scroll', 'inherit' ), true ) ? $value['attachment'] : '';
		$value['size']       = in_array( $value['size'], array( 'inherit', 'cover', 'contain' ), true ) ? $value['size'] : '';

		return $value;
	}

	/**
	 * Sanitize text field.
	 *
	 * @param  string|array $value The submitted value.
	 * @return string|array
	 */
	private function sanitize_text( $value ) {
		return is_array( $value ) ? array_map( __METHOD__, $value ) : sanitize_text_field( $value );
	}

	/**
	 * Sanitize file, image field.
	 *
	 * @param  array $value The submitted value.
	 * @param  array $field The field settings.
	 * @return array
	 */
	private function sanitize_file( $value, $field ) {
		return $field['upload_dir'] ? array_map( 'esc_url_raw', $value ) : $this->sanitize_object( $value );
	}

	/**
	 * Sanitize slider field.
	 *
	 * @param  mixed $value The submitted value.
	 * @param  array $field The field settings.
	 * @return string|int|float
	 */
	private function sanitize_slider( $value, $field ) {
		return true === $field['js_options']['range'] ? sanitize_text_field( $value ) : $this->sanitize_number( $value );
	}

	/**
	 * Sanitize datetime field.
	 *
	 * @param  mixed $value The submitted value.
	 * @param  array $field The field settings.
	 * @return float|string
	 */
	private function sanitize_datetime( $value, $field ) {
		return $field['timestamp'] ? floor( abs( (float) $value ) ) : sanitize_text_field( $value );
	}

	/**
	 * Sanitize map field.
	 *
	 * @param  mixed $value The submitted value.
	 * @return string
	 */
	private function sanitize_map( $value ) {
		$value                               = sanitize_text_field( $value );
		list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );

		$latitude  = (float) $latitude;
		$longitude = (float) $longitude;
		$zoom      = (int) $zoom;

		return "$latitude,$longitude,$zoom";
	}

	/**
	 * Sanitize taxonomy advanced field.
	 *
	 * @param  mixed $value The submitted value.
	 * @return string
	 */
	private function sanitize_taxonomy_advanced( $value ) {
		$value = RWMB_Helpers_Array::from_csv( $value );
		$value = array_filter( array_map( 'absint', $value ) );

		return implode( ',', $value );
	}

	/**
	 * Sanitize URL field.
	 *
	 * @param  string $value The submitted value.
	 * @return string
	 */
	private function sanitize_url( $value ) {
		return esc_url_raw( $value );
	}
}
