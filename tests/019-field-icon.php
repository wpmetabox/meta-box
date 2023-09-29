<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_function_name' );

function your_prefix_function_name( $meta_boxes ) {
	$prefix = '';

	$meta_boxes[] = [
		'title'  => __( 'Test field icon' ),
		'id'     => 'Test Icon',
		'fields' => [
			[
				'name'     => __( 'Icon', 'your-text-domain' ),
				'id'       => $prefix . 'icon_sda545',
				'type'     => 'icon',
				'icon_set' => 'fontawesome',
				'options'  => [
					'fa-solid fa-user'             => __( 'fa-solid fa-user', 'your-text-domain' ),
					'fa-solid fa-magnifying-glass' => __( 'fa-solid fa-magnifying-glass', 'your-text-domain' ),
					'fa-brands fa-instagram'       => __( 'fa-brands fa-instagram', 'your-text-domain' ),
					'fa-solid fa-music'            => __( 'fa-solid fa-music', 'your-text-domain' ),
					'fa-solid fa-cloud'            => __( 'fa-solid fa-cloud', 'your-text-domain' ),
				],
			],
		],
	];

	return $meta_boxes;
}
