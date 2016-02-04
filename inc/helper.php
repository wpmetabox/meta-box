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

	/**
	 * Get post meta.
	 *
	 * @param string   $key     Meta key. Required.
	 * @param int|null $post_id Post ID. null for current post. Optional
	 * @param array    $args    Array of arguments. Optional.
	 * @return mixed
	 */
	static function meta( $key, $args = array(), $post_id = null )
	{
		$args = wp_parse_args( $args, array(
			'type' => 'text',
		) );
		$meta = in_array( $args['type'], array( 'oembed', 'map' ) ) ?
			rwmb_the_field( $key, $args, $post_id, false ) :
			rwmb_get_field( $key, $args, $post_id );
		return apply_filters( 'rwmb_meta', $meta, $key, $args, $post_id );
	}
}
