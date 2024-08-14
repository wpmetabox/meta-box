<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title'  => 'Clone: empty start = true',
		'fields' => [
			[
				'type'              => 'text',
				'name'              => 'Text',
				'id'                => 't1',
				'std'               => 'Default value',
				'clone'             => true,
				'clone_default'     => true,
				'clone_empty_start' => true,
			],
			[
				'type'              => 'select',
				'name'              => 'Select',
				'id'                => 's1',
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
				'id'                => 'g1',
				'name'              => 'Group',
				'clone'             => true,
				'clone_empty_start' => true,
				'fields'            => [
					[
						'type'          => 'text',
						'name'          => 'Text',
						'id'            => 'g1t1',
						'std'           => 'Default value',
						'clone_default' => true,
					],
					[
						'type'          => 'select',
						'name'          => 'Select',
						'id'            => 'g1s1',
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
						'id'            => 'g1r1',
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
						'id'            => 'g1c1',
						'std'           => 1,
						'clone_default' => true,
					],
					[
						'type'              => 'group',
						'id'                => 'g1g1',
						'name'              => 'Group',
						'clone'             => true,
						'clone_empty_start' => true,
						'fields'            => [
							[
								'type'          => 'text',
								'name'          => 'Text',
								'id'            => 'g1g1t1',
								'std'           => 'Default value',
								'clone_default' => true,
							],
							[
								'type'          => 'select_advanced',
								'name'          => 'Select',
								'id'            => 'g1g1s1',
								'options'       => [
									'us' => 'USA',
									'fr' => 'France',
									'gb' => 'Great Britain',
								],
								'std'           => 'fr',
								'clone_default' => true,
							],
						],
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
				'id'            => 't2',
				'std'           => 'Default value',
				'clone'         => true,
				'clone_default' => true,
			],
			[
				'type'          => 'select',
				'name'          => 'Select',
				'id'            => 's2',
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
				'id'     => 'g2',
				'name'   => 'Group',
				'clone'  => true,
				'fields' => [
					[
						'type'          => 'text',
						'name'          => 'Text',
						'id'            => 'g2t2',
						'std'           => 'Default value',
						'clone_default' => true,
					],
					[
						'type'          => 'select',
						'name'          => 'Select',
						'id'            => 'g2s2',
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
						'id'            => 'g2r2',
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
						'id'            => 'g2c2',
						'std'           => 1,
						'clone_default' => true,
					],
					[
						'type'              => 'group',
						'id'                => 'g2g2',
						'name'              => 'Group',
						'clone'             => true,
						'clone_empty_start' => true,
						'fields'            => [
							[
								'type'          => 'text',
								'name'          => 'Text',
								'id'            => 'g2g2t2',
								'std'           => 'Default value',
								'clone_default' => true,
							],
							[
								'type'          => 'select_advanced',
								'name'          => 'Select',
								'id'            => 'g2g2s2',
								'options'       => [
									'us' => 'USA',
									'fr' => 'France',
									'gb' => 'Great Britain',
								],
								'std'           => 'fr',
								'clone_default' => true,
							],
						],
					],
				],
			],
		],
	];

	return $meta_boxes;
} );
