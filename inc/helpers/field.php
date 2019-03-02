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
		$type = isset( $field['type'] ) ? $field['type'] : 'input';
		$map  = array(
			'file_advanced'  => 'media',
			'plupload_image' => 'image_upload',
			'url'            => 'text',
		);

		return isset( $map[ $type ] ) ? $map[ $type ] : $type;
	}
}
