<?php
/**
 * This file demonstrates how to use 'post' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_post_demo' );
function your_prefix_post_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Post Field Demo', 'meta-box' ),

		'fields' => array(
			array(
				'name'        => __( 'Post', 'meta-box' ),
				'id'          => 'post',
				'type'        => 'post',

				// Post type
				'post_type'   => 'page',

				// Field type, either 'select' or 'select_advanced' (default)
				'field_type'  => 'select_advanced',

				// Placeholder
				'placeholder' => __( 'Select an Item', 'meta-box' ),

				// Query arguments (optional). No settings means get all published posts
				'query_args'  => array(
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
				)
			),
		)
	);

	return $meta_boxes;
}


