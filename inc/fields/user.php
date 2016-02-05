<?php
/**
 * User field class.
 */
class RWMB_User_Field extends RWMB_Object_Choice_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		/**
		 * Set default field args
		 */
		$field = wp_parse_args( $field, array(
			'field_type' => 'select',
			'query_args' => array(),
		) );

		/**
		 * Prevent select tree for user since it's not hierarchical
		 */
		$field['field_type'] = 'select_tree' === $field['field_type'] ? 'select' : $field['field_type'];

		/**
		 * Set to always flat
		 */
		$field['flatten'] = true;

		/**
		 * Set default placeholder
		 */
		$field['placeholder'] = empty( $field['placeholder'] ) ? __( 'Select an user', 'meta-box' ) : $field['placeholder'];

		/**
		 * Set default query args
		 */
		$field['query_args'] = wp_parse_args( $field['query_args'], array(
			'orderby' => 'display_name',
			'order'   => 'asc',
			'role'    => '',
			'fields'  => 'all',
		) );
		$field               = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get users
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function get_options( $field )
	{
		$options = get_users( $field['query_args'] );
		return $options;
	}

	/**
	 * Get field names of object to be used by walker
	 *
	 * @return array
	 */
	static function get_db_fields()
	{
		return array(
			'parent' => 'parent',
			'id'     => 'ID',
			'label'  => 'display_name',
		);
	}

	/**
	 * Get option label to display in the frontend
	 *
	 * @param int   $value Option value
	 * @param int   $index Array index
	 * @param array $field Field parameter
	 *
	 * @return string
	 */
	static function get_option_label( &$value, $index, $field )
	{
		$user  = get_userdata( $value );
		$value = '<a href="' . get_author_posts_url( $value ) . '">' . $user->display_name . '</a>';
	}
}
