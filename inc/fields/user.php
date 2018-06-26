<?php
/**
 * The user select field.
 *
 * @package Meta Box
 */

/**
 * User field class.
 */
class RWMB_User_Field extends RWMB_Object_Choice_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		// Set default field args.
		$field = wp_parse_args( $field, array(
			'placeholder' => __( 'Select an user', 'meta-box' ),
		) );
		$field = parent::normalize( $field );

		// Prevent select tree for user since it's not hierarchical.
		$field['field_type'] = 'select_tree' === $field['field_type'] ? 'select' : $field['field_type'];

		// Set to always flat.
		$field['flatten'] = true;

		// Set default query args.
		$field['query_args'] = wp_parse_args( $field['query_args'], array(
			'orderby' => 'display_name',
			'order'   => 'asc',
			'role'    => '',
			'fields'  => 'all',
		) );

		return $field;
	}

	/**
	 * Get users.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function get_options( $field ) {
		$query = new WP_User_Query( $field['query_args'] );
		return $query->get_results();
	}

	/**
	 * Get field names of object to be used by walker.
	 *
	 * @return array
	 */
	public static function get_db_fields() {
		return array(
			'parent' => 'parent',
			'id'     => 'ID',
			'label'  => 'display_name',
		);
	}

	/**
	 * Get option label.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value Option value.
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value ) {
		$user  = get_userdata( $value );
		return '<a href="' . get_author_posts_url( $value ) . '">' . $user->display_name . '</a>';
	}
}
