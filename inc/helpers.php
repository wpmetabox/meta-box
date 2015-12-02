<?php
/**
 * This file contains all helpers/public functions
 * that can be used both on the back-end or front-end
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Helper' ) )
{
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
			// Get all meta boxes registered with 'rwmb_meta_boxes' hook
			$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );

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
}

if ( ! function_exists( 'rwmb_meta' ) )
{
	/**
	 * Get post meta
	 *
	 * @param string   $key     Meta key. Required.
	 * @param int|null $post_id Post ID. null for current post. Optional
	 * @param array    $args    Array of arguments. Optional.
	 *
	 * @return mixed
	 */
	function rwmb_meta( $key, $args = array(), $post_id = null )
	{
		return RWMB_Helper::meta( $key, $args, $post_id );
	}
}

if ( ! function_exists( 'rwmb_get_field' ) )
{
	/**
	 * Get value of custom field.
	 * This is used to replace old version of rwmb_meta key.
	 *
	 * @param  string   $field_id Field ID. Required.
	 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details
	 * @param  int|null $post_id  Post ID. null for current post. Optional.
	 *
	 * @return mixed false if field doesn't exist. Field value otherwise.
	 */
	function rwmb_get_field( $field_id, $args = array(), $post_id = null )
	{
		$field = RWMB_Helper::find_field( $field_id );

		// Get field value
		$value = $field ? call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'get_value' ), $field, $args, $post_id ) : false;

		/**
		 * Allow developers to change the returned value of field
		 *
		 * @param mixed    $value   Field value
		 * @param array    $field   Field parameter
		 * @param array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param int|null $post_id Post ID. null for current post. Optional.
		 */
		$value = apply_filters( 'rwmb_get_field', $value, $field, $args, $post_id );

		return $value;
	}
}

if ( ! function_exists( 'rwmb_the_field' ) )
{
	/**
	 * Display the value of a field
	 *
	 * @param  string   $field_id Field ID. Required.
	 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details
	 * @param  int|null $post_id  Post ID. null for current post. Optional.
	 * @param  bool     $echo     Display field meta value? Default `true` which works in almost all cases. We use `false` for  the [rwmb_meta] shortcode
	 *
	 * @return string
	 */
	function rwmb_the_field( $field_id, $args = array(), $post_id = null, $echo = true )
	{
		// Find field
		$field = RWMB_Helper::find_field( $field_id );

		if ( ! $field )
			return '';

		$output = call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'the_value' ), $field, $args, $post_id );

		/**
		 * Allow developers to change the returned value of field
		 *
		 * @param mixed    $value   Field HTML output
		 * @param array    $field   Field parameter
		 * @param array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param int|null $post_id Post ID. null for current post. Optional.
		 */
		$output = apply_filters( 'rwmb_the_field', $output, $field, $args, $post_id );

		if ( $echo )
			echo $output;

		return $output;
	}
}

if ( ! function_exists( 'rwmb_meta_shortcode' ) )
{
	/**
	 * Shortcode to display meta value
	 *
	 * @param $atts Array of shortcode attributes, same as meta() function, but has more "meta_key" parameter
	 *
	 * @see meta() function below
	 *
	 * @return string
	 */
	function rwmb_meta_shortcode( $atts )
	{
		$atts = wp_parse_args( $atts, array(
			'post_id' => get_the_ID(),
		) );
		if ( empty( $atts['meta_key'] ) )
			return '';

		$field_id = $atts['meta_key'];
		$post_id  = $atts['post_id'];
		unset( $atts['meta_key'], $atts['post_id'] );

		return rwmb_the_field( $field_id, $atts, $post_id, false );
	}

	add_shortcode( 'rwmb_meta', 'rwmb_meta_shortcode' );
}
