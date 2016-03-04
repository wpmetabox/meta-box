<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes )
{
	$meta_boxes[] = [
		'title'  => 'Clone WYSIWYG',
		'fields' => [
			// Default
			[
				'name'  => 'Editor Default',
				'id'    => 'wysiwyg1',
				'type'  => 'wysiwyg',
				'clone' => true,
			],
			// Simple
			[
				'name'    => 'Editor Simple',
				'id'      => 'wysiwyg2',
				'type'    => 'wysiwyg',
				'clone'   => true,
				'options' => [
					'media_buttons' => false,
					'teeny'         => true,
					'quicktags'     => false,
				],
			],
			// Sort clone
			[
				'name'       => 'Clone',
				'id'         => 'wysiwyg3',
				'type'       => 'wysiwyg',
				'clone'      => true,
				'sort_clone' => true,
			],
		],
	];
	if ( is_plugin_active( 'meta-box-group/meta-box-group.php' ) )
	{
		$meta_boxes[] = [
			'title'  => 'Clone WYSIWYG with Group extension',
			'fields' => [
				// Clone, within a non-clone group
				[
					'name'   => 'Non-clone group w/ cloned editor',
					'id'     => 'group1',
					'type'   => 'group',
					'fields' => [
						[
							'name' => 'Text',
							'id'   => 'text',
							'type' => 'text',
						],
						[
							'name'  => 'Editor',
							'id'    => 'wysiwyg',
							'type'  => 'wysiwyg',
							'clone' => true,
						],
					],
				],
				// Non-clone, within a clone group
				[
					'name'   => 'Clone group w/ non-cloned editor',
					'id'     => 'group2',
					'type'   => 'group',
					'clone'  => true,
					'fields' => [
						[
							'name' => 'Text',
							'id'   => 'text',
							'type' => 'text',
						],
						[
							'name' => 'Editor',
							'id'   => 'wysiwyg',
							'type' => 'wysiwyg',
						],
					],
				],
				// Clone, within a clone group
				[
					'name'   => 'Clone group w/ cloned editor',
					'id'     => 'group3',
					'type'   => 'group',
					'fields' => [
						[
							'name' => 'Text',
							'id'   => 'text',
							'type' => 'text',
						],
						[
							'name'  => 'Editor',
							'id'    => 'wysiwyg',
							'type'  => 'wysiwyg',
							'clone' => true,
						],
					],
				],
			],
		];
	}
	return $meta_boxes;
} );
