<?php

// This file shows a demo for register meta boxes for ALL custom post types

add_action( 'admin_init', 'YOUR_PREFIX_register_meta_boxes' );

function YOUR_PREFIX_register_meta_boxes()
{
	if ( ! class_exists( 'RW_Meta_Box' ) )
		return;

	$prefix     = 'YOUR_PREFIX_';
	$meta_boxes = array();

	$post_types = get_post_types();

	// 1st meta box
	$meta_boxes[] = array(
		'id'    => 'personal',
		'title' => 'Personal Information',
		'pages' => $post_types,

		'fields' => array(
			array(
				'name' => 'Full name',
				'id'   => $prefix . 'fname',
				'type' => 'text',
			),
			// Other fields go here
		)
	);
	// Other meta boxes go here

	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}