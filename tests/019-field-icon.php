<?php
add_filter( 'rwmb_meta_boxes', 'field_icon' );

function field_icon( $meta_boxes ) {
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
				'icon_set'    => 'font-awesome-free',
			],
			[
				'name'        => __( 'Icon with svg ouput', 'your-text-domain' ),
				'id'          => $prefix . 'icon_svg_sda545',
				'placeholder' => 'Select svg Icon',
				'type'        => 'icon',
				'icon_set'    => 'font-awesome-free',
			],
			[
				'name'        => __( 'Icon Clone', 'your-text-domain' ),
				'id'          => $prefix . 'icon_clone',
				'placeholder' => 'Select Icon Clone',
				'type'        => 'icon',
				'icon_set'    => 'font-awesome-free',
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
						'icon_set'    => 'font-awesome-free',
					],
					[
						'name'        => __( 'Icon Clone in group', 'your-text-domain' ),
						'id'          => $prefix . 'icon_clone_in_group',
						'placeholder' => 'Select Icon',
						'type'        => 'icon',
						'icon_set'    => 'font-awesome-free',
						'clone'       => true,
					],
				],
			],
		],
	];

	return $meta_boxes;
}

add_filter( 'rwmb_meta_boxes', 'field_custom_icon' );
function field_custom_icon( $meta_boxes ) {
	$prefix = '';

	$meta_boxes[] = [
		'title'  => __( 'Test field icon custom' ),
		'id'     => 'test_icon_custom',
		'fields' => [
			[
				'name'           => __( 'Icon custom', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_func',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-1',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome.json',
				// icon_style by function
				'icon_style' => function () {
					wp_enqueue_style( 'rwmb-custom-icon', RWMB_URL . 'tests/assets/css/line-awesome.min.css', [], RWMB_VER );
				},
			],
			[
				'name'           => __( 'Icon custom By Css', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_link_css',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-2',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome.json',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
			],
			[
				'name'           => __( 'Icon custom with Input type 1 options', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_type_1_opt',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-3',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome-type-1.text',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
			],
			[
				'name'           => __( 'Icon custom with Input type 2 options', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_type_2_opt',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-4',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome-type-2.json',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
			],
			[
				'name'           => __( 'Icon custom with Input type 3 options', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_type_3_opt',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-5',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome-type-3.json',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
			],
			[
				'name'           => __( 'Icon custom with svg ouput type 1', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_svg_type_1',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-6',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome-svg-1.json',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
			],
			[
				'name'           => __( 'Icon custom with svg ouput type 2', 'your-text-domain' ),
				'id'             => $prefix . 'icon_custom_svg_type_2',
				'placeholder'    => 'Select Icon',
				'type'           => 'icon',
				'icon_set'       => 'line-awesome-7',
				'icon_file'      => RWMB_DIR . 'tests/assets/line-awesome-svg-2.json',
				// icon_style by link css
				'icon_style' => RWMB_URL . 'tests/assets/css/line-awesome.min.css',
				'svg_dir'        => RWMB_DIR . 'tests/assets/svg/',
			],
		],
	];

	return $meta_boxes;
}