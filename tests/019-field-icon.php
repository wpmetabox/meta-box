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
				'name'        => __( 'Icon custom', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_func',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome.json',
				// Manually enqueue icon font's CSS.
				'icon_css'    => function () {
					wp_enqueue_style( 'line-awesome', 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css', [], '1.3.0' );
				},
			],
			[
				'name'        => __( 'Icon custom By Css', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_link_css',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome.json',
				'icon_css'    => 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css',
			],
			[
				'name'        => __( 'Icon custom with Input type 1 options', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_type_1_opt',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome-type-1.text',
				'icon_css'    => 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css',
			],
			[
				'name'        => __( 'Icon custom with Input type 2 options', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_type_2_opt',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome-type-2.json',
				'icon_css'    => 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css',
			],
			[
				'name'        => __( 'Icon custom with Input type 3 options', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_type_3_opt',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome-type-3.json',
				'icon_css'    => 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css',
			],
			[
				'name'        => __( 'Icon custom with svg ouput type 1', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_svg_type_1',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome-svg-1.json',
				'icon_css'    => 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css',
			],
			[
				'name'        => __( 'Icon custom with svg ouput type 2', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_svg_type_2',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
                'icon_file'   => RWMB_DIR . 'tests/assets/line-awesome-svg-2.json',
				'icon_dir'    => RWMB_DIR . 'tests/assets/svg/',
			],
            [
				'name'        => __( 'Icon custom with icon_dir and without icon_file', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_without_file',
				'placeholder' => 'Select Icon',
				'type'        => 'icon',
				'icon_file'   => '',
				'icon_dir'    => RWMB_DIR . 'tests/assets/svg/',
			],
		],
	];

	return $meta_boxes;
}