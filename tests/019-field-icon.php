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

add_filter( 'rwmb_meta_boxes', 'field_custom_icon' );
function field_custom_icon( $meta_boxes ) {
	$prefix = '';

	$meta_boxes[] = [
		'title' => __('Test field icon custom'),
		'id' => 'test_icon_custom',
		'fields' => [
			[
				'name' => __('Icon custom', 'your-text-domain'),
				'id' => $prefix . 'icon_custom_sda545',
				'placeholder' => 'Select Icon',
				'type' => 'icon',
				'icon_set' => 'Line Awesome',
				'icon_json' => RWMB_DIR . 'tests/assets/line-awesome.json',
				'enqueue_script' => function () {
					wp_enqueue_style( 'rwmb-custom-icon', RWMB_URL . 'tests/assets/css/line-awesome.min.css', [], RWMB_VER );
				},
			],
			[
				'name'        => __( 'Icon Clone', 'your-text-domain' ),
				'id'          => $prefix . 'icon_custom_clone',
				'placeholder' => 'Select Icon Clone',
				'type'        => 'icon',
				'icon_set' => 'Line Awesome',
				'icon_json' => RWMB_DIR . 'tests/assets/line-awesome.json',
				'enqueue_script' => function () {
					wp_enqueue_style( 'rwmb-custom-icon', RWMB_URL . 'tests/assets/css/line-awesome.min.css', [], RWMB_VER );
				},
				'clone'       => true,
			],
			[
				'name'   => __( 'Group Icon', 'your-text-domain' ),
				'id'     => $prefix . 'group_custom_icon',
				'type'   => 'group',
				'fields' => [
					[
						'name'        => __( 'Icon in group', 'your-text-domain' ),
						'id'          => $prefix . 'icon_custom_in_group',
						'placeholder' => 'Select Icon',
						'type'        => 'icon',
						'icon_set' => 'Line Awesome',
                        'icon_json' => RWMB_DIR . 'tests/assets/line-awesome.json',
                        'enqueue_script' => function () {
                            wp_enqueue_style( 'rwmb-custom-icon', RWMB_URL . 'tests/assets/css/line-awesome.min.css', [], RWMB_VER );
                        },
					],
					[
						'name'        => __( 'Icon Clone in group', 'your-text-domain' ),
						'id'          => $prefix . 'icon_custom_clone_in_group',
						'placeholder' => 'Select Icon',
						'type'        => 'icon',
						'icon_set' => 'Line Awesome',
                        'icon_json' => RWMB_DIR . 'tests/assets/line-awesome.json',
                        'enqueue_script' => function () {
                            wp_enqueue_style( 'rwmb-custom-icon', RWMB_URL . 'tests/assets/css/line-awesome.min.css', [], RWMB_VER );
                        },
						'clone'       => true,
					],
				],
			],
		],
	];

	return $meta_boxes;
}