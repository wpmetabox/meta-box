<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes )
{
	if ( !is_plugin_active( 'meta-box-group/meta-box-group.php' ) )
	{
		return $meta_boxes;
	}
	$meta_boxes[] = [
		'title'  => 'Group clone',
		'fields' => [
			// Non-clone group w/ clone fields
			[
				'name'   => 'Non-clone group w/ cloned fields',
				'id'     => 'group1',
				'type'   => 'group',
				'fields' => [
					[
						'name'  => 'Text',
						'id'    => 'text',
						'type'  => 'text',
						'clone' => true,
					],
					[
						'name'    => 'Checkbox List',
						'id'      => 'cblist',
						'type'    => 'checkbox_list',
						'clone'   => true,
						'options' => [
							'asia'      => 'Asia',
							'europe'    => 'Europe',
							'america'   => 'America',
							'australia' => 'Australia',
							'africa'    => 'Africa',
						],
					],
					[
						'name'      => 'Post',
						'id'        => 'post',
						'type'      => 'post',
						'post_type' => 'post',
						'clone'     => true,
					],
					[
						'name'  => 'Editor',
						'id'    => 'wysiwyg',
						'type'  => 'wysiwyg',
						'clone' => true,
					],
				],
			],
			// Clone group w/ non-clone fields
			[
				'name'   => 'Clone group w/ non-clone fields',
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
						'name'    => 'Checkbox List',
						'id'      => 'cblist',
						'type'    => 'checkbox_list',
						'options' => [
							'asia'      => 'Asia',
							'europe'    => 'Europe',
							'america'   => 'America',
							'australia' => 'Australia',
							'africa'    => 'Africa',
						],
					],
					[
						'name'      => 'Post',
						'id'        => 'post',
						'type'      => 'post',
						'post_type' => 'post',
					],
					[
						'name' => 'Editor',
						'id'   => 'wysiwyg',
						'type' => 'wysiwyg',
					],
				],
			],
			// Clone group w/ clone fields
			[
				'name'   => 'Non-clone group w/ cloned fields',
				'id'     => 'group3',
				'type'   => 'group',
				'clone'  => true,
				'fields' => [
					[
						'name'  => 'Text',
						'id'    => 'text',
						'type'  => 'text',
						'clone' => true,
					],
					[
						'name'    => 'Checkbox List',
						'id'      => 'cblist',
						'type'    => 'checkbox_list',
						'clone'   => true,
						'options' => [
							'asia'      => 'Asia',
							'europe'    => 'Europe',
							'america'   => 'America',
							'australia' => 'Australia',
							'africa'    => 'Africa',
						],
					],
					[
						'name'      => 'Post',
						'id'        => 'post',
						'type'      => 'post',
						'post_type' => 'post',
						'clone'     => true,
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
	return $meta_boxes;
} );
