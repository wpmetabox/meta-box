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
	 * @param string $post_type Post type
	 */
	public static function hash_fields( $post_type )
	{
		self::$fields[$post_type] = array();

		$meta_boxes = RWMB_Core::get_meta_boxes();
		foreach ( $meta_boxes as $meta_box )
		{
			$meta_box = RW_Meta_Box::normalize( $meta_box );
			if ( ! in_array( $post_type, $meta_box['post_types'] ) )
			{
				continue;
			}
			foreach ( $meta_box['fields'] as $field )
			{
				if ( ! empty( $field['id'] ) )
				{
					self::$fields[$post_type][$field['id']] = $field;
				}
			}
		}
	}

	/**
	 * Find field by field ID.
	 * This function finds field in meta boxes registered by 'rwmb_meta_boxes' filter.
	 *
	 * @param string $field_id Field ID
	 * @param int    $post_id
	 * @return array|false Field params (array) if success. False otherwise.
	 */
	public static function find_field( $field_id, $post_id = null )
	{
		$post_type = get_post_type( $post_id );
		if ( empty( self::$fields[$post_type] ) )
		{
			self::hash_fields( $post_type );
		}
		$fields = self::$fields[$post_type];
		if ( ! isset( $fields[$field_id] ) )
		{
			return false;
		}
		$field = $fields[$field_id];
		return RWMB_Field::call( 'normalize', $field );
	}

	/**
	 * Get post meta
	 *
	 * @param string   $key     Meta key. Required.
	 * @param int|null $post_id Post ID. null for current post. Optional
	 * @param array    $args    Array of arguments. Optional.
	 *
	 * @return mixed
	 */
	public static function meta( $key, $args = array(), $post_id = null )
	{
		$post_id = empty( $post_id ) ? get_the_ID() : $post_id;
		$args    = wp_parse_args( $args, array(
			'type'     => 'text',
			'multiple' => false,
			'clone'    => false,
		) );
		// Always set 'multiple' true for following field types
		if ( in_array( $args['type'], array( 'checkbox_list', 'autocomplete', 'file', 'file_advanced', 'image', 'image_advanced', 'plupload_image', 'thickbox_image' ) ) )
		{
			$args['multiple'] = true;
		}

		$field = array(
			'id'       => $key,
			'type'     => $args['type'],
			'clone'    => $args['clone'],
			'multiple' => $args['multiple'],
		);

		switch ( $args['type'] )
		{
			case 'taxonomy_advanced':
				if ( empty( $args['taxonomy'] ) )
				{
					break;
				}
				$meta     = get_post_meta( $post_id, $key, ! $args['multiple'] );
				$term_ids = wp_parse_id_list( $meta );
				// Allow to pass more arguments to "get_terms"
				$func_args = wp_parse_args( array(
					'include'    => $term_ids,
					'hide_empty' => false,
				), $args );
				unset( $func_args['type'], $func_args['taxonomy'], $func_args['multiple'] );
				$meta = get_terms( $args['taxonomy'], $func_args );
				break;
			case 'taxonomy':
				$meta = empty( $args['taxonomy'] ) ? array() : get_the_terms( $post_id, $args['taxonomy'] );
				break;
			case 'map':
				$field = array(
					'id'       => $key,
					'multiple' => false,
					'clone'    => false,
				);
				$meta  = RWMB_Map_Field::the_value( $field, $args, $post_id );
				break;
			case 'oembed':
				$meta = RWMB_OEmbed_Field::the_value( $field, $args, $post_id );
				break;
			default:
				$meta = RWMB_Field::call( 'get_value', $field, $args, $post_id );
		}
		return apply_filters( 'rwmb_meta', $meta, $key, $args, $post_id );
	}
}
