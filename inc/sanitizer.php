<?php
/**
 * Sanitize field value before saving.
 */
class RWMB_Sanitizer {
	public function init() {
		add_filter( 'rwmb_sanitize', [ $this, 'sanitize' ], 10, 4 );
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

		$callbacks = [
			'autocomplete'      => [ $this, 'sanitize_choice' ],
			'background'        => [ $this, 'sanitize_background' ],
			'button_group'      => [ $this, 'sanitize_choice' ],
			'checkbox'          => [ $this, 'sanitize_checkbox' ],
			'checkbox_list'     => [ $this, 'sanitize_choice' ],
			'color'             => [ $this, 'sanitize_color' ],
			'date'              => [ $this, 'sanitize_datetime' ],
			'datetime'          => [ $this, 'sanitize_datetime' ],
			'email'             => 'sanitize_email',
			'fieldset_text'     => [ $this, 'sanitize_text' ],
			'file'              => [ $this, 'sanitize_file' ],
			'file_advanced'     => [ $this, 'sanitize_object' ],
			'file_input'        => [ $this, 'sanitize_url' ],
			'file_upload'       => [ $this, 'sanitize_object' ],
			'hidden'            => 'sanitize_text_field',
			'image'             => [ $this, 'sanitize_file' ],
			'image_advanced'    => [ $this, 'sanitize_object' ],
			'image_select'      => [ $this, 'sanitize_choice' ],
			'image_upload'      => [ $this, 'sanitize_object' ],
			'key_value'         => [ $this, 'sanitize_text' ],
			'map'               => [ $this, 'sanitize_map' ],
			'number'            => [ $this, 'sanitize_number' ],
			'oembed'            => [ $this, 'sanitize_url' ],
			'osm'               => [ $this, 'sanitize_map' ],
			'password'          => 'sanitize_text_field',
			'post'              => [ $this, 'sanitize_object' ],
			'radio'             => [ $this, 'sanitize_choice' ],
			'range'             => [ $this, 'sanitize_number' ],
			'select'            => [ $this, 'sanitize_choice' ],
			'select_advanced'   => [ $this, 'sanitize_choice' ],
			'sidebar'           => [ $this, 'sanitize_text' ],
			'single_image'      => 'absint',
			'slider'            => [ $this, 'sanitize_slider' ],
			'switch'            => [ $this, 'sanitize_checkbox' ],
			'taxonomy'          => [ $this, 'sanitize_object' ],
			'taxonomy_advanced' => [ $this, 'sanitize_taxonomy_advanced' ],
			'text'              => 'sanitize_text_field',
			'text_list'         => [ $this, 'sanitize_text' ],
			'textarea'          => 'wp_kses_post',
			'time'              => 'sanitize_text_field',
			'url'               => [ $this, 'sanitize_url' ],
			'user'              => [ $this, 'sanitize_object' ],
			'video'             => [ $this, 'sanitize_object' ],
			'wysiwyg'           => 'wp_kses_post',
		];

		$type = $field['type'];

		return $callbacks[ $type ] ?? null;
	}

	/**
	 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string.
	 * This prevents using default value once the checkbox has been unchecked.
	 *
	 * @link https://github.com/rilwis/meta-box/issues/6
	 * @param string $value Checkbox value.
	 */
	private function sanitize_checkbox( $value ): int {
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

	private function sanitize_color( string $value ): string {
		if ( str_contains( $value, 'hsl' ) ) {
			return wp_unslash( $value );
		}

		if ( ! str_contains( $value, 'rgb' ) ) {
			return (string) sanitize_hex_color( $value );
		}

		// rgba value.
		$red   = '';
		$green = '';
		$blue  = '';
		$alpha = 1;

		if ( str_contains( $value, 'rgba' ) ) {
			sscanf( $value, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
		} else {
			sscanf( $value, 'rgb(%d,%d,%d)', $red, $green, $blue );
		}

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
		$value   = wp_unslash( $value );
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
		$value          = wp_parse_args( $value, [
			'color'      => '',
			'image'      => '',
			'repeat'     => '',
			'attachment' => '',
			'position'   => '',
			'size'       => '',
		] );
		$value['color'] = $this->sanitize_color( $value['color'] );
		$value['image'] = esc_url_raw( $value['image'] );

		$value['repeat']     = in_array( $value['repeat'], [ 'no-repeat', 'repeat', 'repeat-x', 'repeat-y', 'inherit' ], true ) ? $value['repeat'] : '';
		$value['position']   = in_array( $value['position'], [ 'top left', 'top center', 'top right', 'center left', 'center center', 'center right', 'bottom left', 'bottom center', 'bottom right' ], true ) ? $value['position'] : '';
		$value['attachment'] = in_array( $value['attachment'], [ 'fixed', 'scroll', 'inherit' ], true ) ? $value['attachment'] : '';
		$value['size']       = in_array( $value['size'], [ 'inherit', 'cover', 'contain' ], true ) ? $value['size'] : '';

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
		return $field['timestamp'] ? (float) $value : sanitize_text_field( $value );
	}

	private function sanitize_map( $value ): string {
		$value                               = sanitize_text_field( $value );
		list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );

		$latitude  = (float) $latitude;
		$longitude = (float) $longitude;
		$zoom      = (int) $zoom;

		return "$latitude,$longitude,$zoom";
	}

	private function sanitize_taxonomy_advanced( $value ): string {
		return implode( ',', wp_parse_id_list( $value ) );
	}

	private function sanitize_url( string $value ): string {
		return esc_url_raw( $value );
	}
}
