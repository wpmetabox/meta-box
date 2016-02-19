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
	 * Find field by field ID.
	 * This function finds field in meta boxes registered by 'rwmb_meta_boxes' filter.
	 *
	 * @param  string $field_id Field ID
	 * @return array|false Field params (array) if success. False otherwise.
	 */
	static function find_field( $field_id )
	{
		$meta_boxes = RWMB_Core::get_meta_boxes();
		foreach ( $meta_boxes as $meta_box )
		{
			$meta_box = RW_Meta_Box::normalize( $meta_box );
			foreach ( $meta_box['fields'] as $field )
			{
				if ( $field_id == $field['id'] )
				{
					return $field;
				}
			}
		}
		return false;
	}
}
