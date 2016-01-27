<?php
/**
 * The helper class.
 */

/**
 * Wrapper class for helper functions
 */
class RWMB_Helper
{
	/**
	 * Find field by field ID
	 * This function finds field in meta boxes registered by 'rwmb_meta_boxes' filter
	 * Note: if users use old code to add meta boxes, this function might not work properly
	 *
	 * @param  string $field_id Field ID
	 *
	 * @return array|false Field params (array) if success. False otherwise.
	 */
	static function find_field( $field_id )
	{
		$meta_boxes = RWMB_Core::get_meta_boxes();

		// Find field
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
	 * Get post meta
	 *
	 * @param string   $key     Meta key. Required.
	 * @param int|null $post_id Post ID. null for current post. Optional
	 * @param array    $args    Array of arguments. Optional.
	 *
	 * @return mixed
	 */
	static function meta( $key, $args = array(), $post_id = null )
	{
		$post_id = empty( $post_id ) ? get_the_ID() : $post_id;
		$args    = wp_parse_args( $args, array(
			'type'     => 'text',
			'multiple' => false,
		) );

		// Always set 'multiple' true for following field types
		if ( in_array( $args['type'], array( 'checkbox_list', 'file', 'file_advanced', 'image', 'image_advanced', 'plupload_image', 'thickbox_image' ) ) )
		{
			$args['multiple'] = true;
		}

		$meta = get_post_meta( $post_id, $key, ! $args['multiple'] );

		// Get uploaded files info
		if ( in_array( $args['type'], array( 'file', 'file_advanced' ) ) )
		{
			if ( is_array( $meta ) && ! empty( $meta ) )
			{
				$files = array();
				foreach ( $meta as $id )
				{
					// Get only info of existing attachments
					if ( get_attached_file( $id ) )
					{
						$files[$id] = RWMB_File_Field::file_info( $id );
					}
				}
				$meta = $files;
			}
		}
		// Get uploaded images info
		elseif ( in_array( $args['type'], array( 'image', 'plupload_image', 'thickbox_image', 'image_advanced' ) ) )
		{
			if ( is_array( $meta ) && ! empty( $meta ) )
			{
				$images = array();
				foreach ( $meta as $id )
				{
					// Get only info of existing attachments
					if ( get_attached_file( $id ) )
					{
						$images[$id] = RWMB_Image_Field::file_info( $id, $args );
					}
				}
				$meta = $images;
			}
		}
		// Get terms
		elseif ( 'taxonomy_advanced' == $args['type'] )
		{
			if ( ! empty( $args['taxonomy'] ) )
			{
				$term_ids = array_map( 'intval', array_filter( explode( ',', $meta . ',' ) ) );
				// Allow to pass more arguments to "get_terms"
				$func_args = wp_parse_args( array(
					'include'    => $term_ids,
					'hide_empty' => false,
				), $args );
				unset( $func_args['type'], $func_args['taxonomy'], $func_args['multiple'] );
				$meta = get_terms( $args['taxonomy'], $func_args );
			}
			else
			{
				$meta = array();
			}
		}
		// Get post terms
		elseif ( 'taxonomy' == $args['type'] )
		{
			$meta = empty( $args['taxonomy'] ) ? array() : get_the_terms( $post_id, $args['taxonomy'] );
		}
		// Get map
		elseif ( 'map' == $args['type'] )
		{
			$field = array(
				'id'       => $key,
				'multiple' => false,
				'clone'    => false,
			);
			$meta  = RWMB_Map_Field::the_value( $field, $args, $post_id );
		}
		// Display oembed content
		elseif ( 'oembed' == $args['type'] )
		{
			$field = array(
				'id'       => $key,
				'clone'    => isset( $args['clone'] ) ? $args['clone'] : false,
				'multiple' => isset( $args['multiple'] ) ? $args['multiple'] : false,
			);
			$meta  = RWMB_OEmbed_Field::the_value( $field, $args, $post_id );
		}
		return apply_filters( 'rwmb_meta', $meta, $key, $args, $post_id );
	}
}
