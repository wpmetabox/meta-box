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
		$field = wp_parse_args( $field, array(
			'post_type'  => 'post',
			'field_type' => 'select',
			'parent'     => false,
			'query_args' => array(),
		) );

		/**
		 * Set default placeholder
		 * - If multiple post types: show 'Select a post'
		 * - If single post type: show 'Select a %post_type_name%'
		 */
		if ( empty( $field['placeholder'] ) )
		{
			$field['placeholder'] = __( 'Select a post', 'meta-box' );
			if ( is_string( $field['post_type'] ) && post_type_exists( $field['post_type'] ) )
			{
				$post_type_object     = get_post_type_object( $field['post_type'] );
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
		$field['query_args']              = wp_parse_args( $field['query_args'], array(
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
		) );
		$field['query_args']['post_type'] = $field['post_type'];


		$field = parent::normalize( $field );

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
	 *
	 * @return array
	 */
	public static function get_options( $field )
	{
		$query = new WP_Query( $field['query_args'] );
		return $query->have_posts() ? $query->posts : array();
	}

	/**
	 * Get post link to display in the frontend
	 *
	 * @param int   $value Option value, e.g. post ID
	 * @param int   $index Array index
	 * @param array $field Field parameter
	 *
	 * @return string
	 */
	public static function get_option_label( &$value, $index, $field )
	{
		$value = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( get_permalink( $value ) ),
			the_title_attribute( array(
				'post' => $value,
				'echo' => false,
			) ),
			get_the_title( $value )
		);
	}

	/**
	 * Output the field value
	 * Display unordered list of option labels, not option values
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string Link(s) to post
	 */
	public static function the_value( $field, $args = array(), $post_id = null )
	{
		return RWMB_Select_Field::the_value( $field, $args, $post_id );
	}
}
