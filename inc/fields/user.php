<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_User_Field extends RWMB_Select_Advanced_Field
{
	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$field['options'] = self::get_options( $field );
		switch ( $field['field_type'] )
		{
			case 'select':
				return RWMB_Select_Field::html( $meta, $field );
				break;
			case 'select_advanced':
			default:
				return RWMB_Select_Advanced_Field::html( $meta, $field );
		}
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = wp_parse_args( $field, array(
			'field_type' => 'select_advanced',
			'parent'     => false,
			'query_args' => array(),
		) );

		$field['std'] = empty( $field['std'] ) ? __( 'Select an user', 'meta-box' ) : $field['std'];

		$field['query_args'] = wp_parse_args( $field['query_args'], array(
			'orderby' => 'display_name',
			'order'   => 'asc',
			'role'    => '',
			'fields'  => 'all',
		) );

		switch ( $field['field_type'] )
		{
			case 'select':
				return RWMB_Select_Field::normalize( $field );
				break;
			case 'select_advanced':
			default:
				return RWMB_Select_Advanced_Field::normalize( $field );
		}
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
		$results = get_users( $field['query_args'] );
		$options = array();
		foreach ( $results as $result )
		{
			$options[$result->ID] = $result->display_name;
		}

		return $options;
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
