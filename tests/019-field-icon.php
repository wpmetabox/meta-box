<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_function_name' );

function your_prefix_function_name( $meta_boxes ) {
	$prefix = '';

	$meta_boxes[] = [
		'title'  => __( 'Test field icon' ),
		'id'     => 'test_icon',
		'fields' => [
			[
				'name'     => __( 'Icon', 'your-text-domain' ),
				'id'       => $prefix . 'icon_sda545',
				'placeholder' => 'Select Icon',
				'type'     => 'icon',
				'icon_set' => 'fontawesome'
			],
		],
	];

	return $meta_boxes;
}
