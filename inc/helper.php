<?php
/**
 * The helper class.
 */

/**
 * Wrapper class for helper functions.
 */
class RWMB_Helper
{
	/**
	 * Stores all registered fields
	 * @var array
	 */
	private static $fields = array();

	/**
	 * Hash all fields into an indexed array for search
	 *
	 */
	static function hash_fields()
	{
		$meta_boxes = RWMB_Core::get_meta_boxes();
		foreach ( $meta_boxes as $meta_box )
		{
			foreach ( $meta_box['fields'] as $field )
			{
		 		self::$fields[ $field['id'] ] = $field;
			}
		}
	}

	/**
	 * Find field by field ID.
	 * This function finds field in meta boxes registered by 'rwmb_meta_boxes' filter.
	 *
	 * @param  string $field_id Field ID
	 * @return array|false Field params (array) if success. False otherwise.
	 */
	static function find_field( $field_id )
	{
		if( empty( self::$fields ) )
		{
			self::hash_fields();
		}

		$field = isset( self::$fields[ $field_id ] ) ? self::$fields[ $field_id ]  : false;
		return $field ? call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'normalize' ), $field ) : false;
	}
}
