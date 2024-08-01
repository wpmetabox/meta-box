<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title'  => 'Clone: empty start = true',
		'fields' => [
			[
				'type'              => 'text',
				'name'              => 'Text',
				'id'                => 'text',
				'std'               => 'Default value',
				'clone'             => true,
				'clone_default'     => true,
				'clone_empty_start' => true,
			],
			[
				'type'              => 'select',
				'name'              => 'Select',
				'id'                => 'select',
				'options'           => [
					'us' => 'USA',
					'fr' => 'France',
					'gb' => 'Great Britain',
				],
				'std'               => 'fr',
				'clone'             => true,
				'clone_default'     => true,
				'clone_empty_start' => true,
			],
			[
				'type'              => 'group',
				'id'                => 'group',
				'name'              => 'Group',
				'clone'             => true,
				'clone_empty_start' => true,
				'fields'            => [
					[
						'type'          => 'text',
						'name'          => 'Text',
						'id'            => 'text2',
						'std'           => 'Default value',
						'clone_default' => true,
					],
					[
						'type'          => 'select',
						'name'          => 'Select',
						'id'            => 'select2',
						'options'       => [
							'us' => 'USA',
							'fr' => 'France',
							'gb' => 'Great Britain',
						],
						'std'           => 'fr',
						'clone_default' => true,
					],
					[
						'type'          => 'radio',
						'name'          => 'Radio',
						'id'            => 'radio2',
						'options'       => [
							'us' => 'USA',
							'fr' => 'France',
							'gb' => 'Great Britain',
						],
						'std'           => 'fr',
						'clone_default' => true,
					],
					[
						'type'          => 'checkbox',
						'name'          => 'Checkbox',
						'id'            => 'checkbox2',
						'std'           => 1,
						'clone_default' => true,
					],
				],
			],
		],
	];

	$meta_boxes[] = [
		'title'  => 'Clone: empty start = false',
		'fields' => [
			[
				'type'          => 'text',
				'name'          => 'Text',
				'id'            => 'text1',
				'std'           => 'Default value',
				'clone'         => true,
				'clone_default' => true,
			],
			[
				'type'          => 'select',
				'name'          => 'Select',
				'id'            => 'select1',
				'options'       => [
					'us' => 'USA',
					'fr' => 'France',
					'gb' => 'Great Britain',
				],
				'std'           => 'fr',
				'clone'         => true,
				'clone_default' => true,
			],
			[
				'type'   => 'group',
				'id'     => 'group1',
				'name'   => 'Group',
				'clone'  => true,
				'fields' => [
					[
						'type'          => 'text',
						'name'          => 'Text',
						'id'            => 'text2',
						'std'           => 'Default value',
						'clone_default' => true,
					],
					[
						'type'          => 'select',
						'name'          => 'Select',
						'id'            => 'select2',
						'options'       => [
							'us' => 'USA',
							'fr' => 'France',
							'gb' => 'Great Britain',
						],
						'std'           => 'fr',
						'clone_default' => true,
					],
					[
						'type'          => 'radio',
						'name'          => 'Radio',
						'id'            => 'radio2',
						'options'       => [
							'us' => 'USA',
							'fr' => 'France',
							'gb' => 'Great Britain',
						],
						'std'           => 'fr',
						'clone_default' => true,
					],
					[
						'type'          => 'checkbox',
						'name'          => 'Checkbox',
						'id'            => 'checkbox2',
						'std'           => 1,
						'clone_default' => true,
					],
				],
			],
		],
	];

	return $meta_boxes;
} );
