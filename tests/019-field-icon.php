<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_function_name' );

function your_prefix_function_name( $meta_boxes ) {
	$prefix = '';

	$meta_boxes[] = [
		'title'  => __( 'Test field icon' ),
		'id'     => 'test_icon',
		'fields' => [
			[
				'name'        => __( 'Icon', 'your-text-domain' ),
				'id'          => $prefix . 'icon_sda545',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_set'    => 'fontawesome',
			],
			[
				'name'        => __( 'Icon Clone', 'your-text-domain' ),
				'id'          => $prefix . 'icon_clone',
				'placeholder' => 'Select Icon Clone',
				'type'        => 'icon',
				'icon_set'    => 'fontawesome',
				'clone'       => true,
			],
			[
				'name'   => __( 'Group Icon', 'your-text-domain' ),
				'id'     => $prefix . 'group_icon',
				'type'   => 'group',
				'fields' => [
					[
						'name'        => __( 'Icon in group', 'your-text-domain' ),
						'id'          => $prefix . 'icon_in_group',
						'placeholder' => 'Select Icon',
						'type'        => 'icon',
						'icon_set'    => 'fontawesome',
					],
					[
						'name'        => __( 'Icon Clone in group', 'your-text-domain' ),
						'id'          => $prefix . 'icon_clone_in_group',
						'placeholder' => 'Select Icon',
						'type'        => 'icon',
						'icon_set'    => 'fontawesome',
						'clone'       => true,
					],
				],
			],
		],
	];

	return $meta_boxes;
}
