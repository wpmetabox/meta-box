<?php
/**
 * Field helper functions.
 *
 * @package Meta Box
 */

/**
 * Field helper class.
 *
 * @package Meta Box
 */
class RWMB_Helpers_Field {
	/**
	 * Localize a script only once.
	 *
	 * @link https://github.com/rilwis/meta-box/issues/850
	 *
	 * @param string $handle Script handle.
	 * @param string $name   Object name.
	 * @param array  $data   Localized data.
	 */
	public static function localize_script_once( $handle, $name, $data ) {
		if ( ! wp_scripts()->get_data( $handle, 'data' ) ) {
			wp_localize_script( $handle, $name, $data );
		}
	}

	/**
	 * Get field class name.
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	public static function get_class( $field ) {
		$type  = self::get_type( $field );
		$class = 'RWMB_' . RWMB_Helpers_String::title_case( $type ) . '_Field';
		return class_exists( $class ) ? $class : 'RWMB_Input_Field';
	}

	/**
	 * Get field type.
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	private static function get_type( $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'text';
		$map  = array_merge(
			array(
				$type => $type,
			),
			array(
				'file_advanced'  => 'media',
				'plupload_image' => 'image_upload',
				'url'            => 'text',
			)
		);

		return $map[ $type ];
	}
}
