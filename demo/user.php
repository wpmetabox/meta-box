<?php
/**
 * This file demonstrates how to use 'user' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_user_demo' );
function your_prefix_user_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'User Field Demo', 'your-prefix' ),

		'fields' => array(
			array(
				'name'        => __( 'User', 'your-prefix' ),
				'id'          => 'user',
				'type'        => 'user',

				// 'clone'       => true,
				// 'multiple'    => true,
				// Field type, either 'select' or 'select_advanced' (default)
				'field_type'  => 'select_advanced',

				// Placeholder
				'placeholder' => __( 'Select an author', 'your-prefix' ),

				// Query arguments (optional). No settings means get all published users
				// @see https://codex.wordpress.org/Function_Reference/get_users
				'query_args'  => array(),
			),
		),
	);

	return $meta_boxes;
}


