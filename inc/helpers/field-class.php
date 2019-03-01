<?php
/**
 * A helper class that handles class names for fields.
 *
 * @package Meta Box
 */

/**
 * Field class helper.
 *
 * @package Meta Box
 */
class RWMB_Helpers_Field_Class {
	/**
	 * Get field class name.
	 *
	 * @param array $field Field settings..
	 * @return string Field class name.
	 */
	public static function get_class_name( $field ) {
		$type  = self::map_types( $field );
		$type  = str_replace( array( '-', '_' ), ' ', $type );
		$class = 'RWMB_' . ucwords( $type ) . '_Field';
		$class = str_replace( ' ', '_', $class );
		return class_exists( $class ) ? $class : 'RWMB_Input_Field';
	}

	/**
	 * Map field types.
	 *
	 * @param array $field Field parameters.
	 * @return string Field mapped type.
	 */
	private static function map_types( $field ) {
		$type     = isset( $field['type'] ) ? $field['type'] : 'input';
		$type_map = apply_filters(
			'rwmb_type_map',
			array(
				'file_advanced'  => 'media',
				'plupload_image' => 'image_upload',
				'url'            => 'text',
			)
		);

		return isset( $type_map[ $type ] ) ? $type_map[ $type ] : $type;
	}
}
