<?php
/**
 * Post field class.
 */
class RWMB_Post_Field extends RWMB_Object_Choice_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	public static function normalize( $field )
	{
		/**
		 * Set default field args
		 */
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'post_type' => 'post',
			'parent'    => false,
		) );

		if ( ! isset( $field['query_args']['post_type'] ) )
			$field['query_args']['post_type'] = $field['post_type'];

		/**
		 * Set default placeholder
		 * - If multiple post types: show 'Select a post'
		 * - If single post type: show 'Select a %post_type_name%'
		 */
		if ( empty( $field['placeholder'] ) )
		{
			$field['placeholder'] = __( 'Select a post', 'meta-box' );
			if ( is_string( $field['query_args']['post_type'] ) && post_type_exists( $field['query_args']['post_type'] ) )
			{
				$post_type_object     = get_post_type_object( $field['query_args']['post_type'] );
				$field['placeholder'] = sprintf( __( 'Select a %s', 'meta-box' ), $post_type_object->labels->singular_name );
			}
		}

		/**
		 * Set parent option, which will change field name to `parent_id` to save as post parent
		 */
		if ( $field['parent'] )
		{
			$field['multiple']   = false;
			$field['field_name'] = 'parent_id';
		}

		/**
		 * Set default query args
		 */
		$field['query_args'] = wp_parse_args( $field['query_args'], array(
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		) );

		return $field;
	}

	/**
	 * Get field names of object to be used by walker
	 *
	 * @return array
	 */
	public static function get_db_fields()
	{
		return array(
			'parent' => 'post_parent',
			'id'     => 'ID',
			'label'  => 'post_title',
		);
	}

	/**
	 * Get meta value
	 * If field is cloneable, value is saved as a single entry in DB
	 * Otherwise value is saved as multiple entries (for backward compatibility)
	 *
	 * @see "save" method for better understanding
	 *
	 * @param $post_id
	 * @param $saved
	 * @param $field
	 *
	 * @return array
	 */
	public static function meta( $post_id, $saved, $field )
	{
		if ( isset( $field['parent'] ) && $field['parent'] )
		{
			$post = get_post( $post_id );
			return $post->post_parent;
		}

		return parent::meta( $post_id, $saved, $field );
	}

	/**
	 * Get options for walker
	 *
	 * @param array $field
	 * @return array
	 */
	public static function get_options( $field )
	{
		$query = new WP_Query( $field['query_args'] );
		return $query->have_posts() ? $query->posts : array();
	}

	/**
	 * Get option label
	 *
	 * @param string $value Option value
	 * @param array  $field Field parameter
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value )
	{
		return sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( get_permalink( $value ) ),
			the_title_attribute( array(
				'post' => $value,
				'echo' => false,
			) ),
			get_the_title( $value )
		);
	}
}
