<?php
add_filter( 'rwmb_meta_boxes', function( $meta_boxes ) {
	$meta_boxes[] = [
		'title' => 'No clone',
		'fields' => [
			[
				'type' => 'taxonomy_advanced',
				'id'   => 'ta1',
				'name' => 'TA1',
			],
			[
				'type'     => 'taxonomy_advanced',
				'id'       => 'ta2',
				'name'     => 'TA2 - Multiple',
				'multiple' => true,
			]
		],
	];
	$meta_boxes[] = [
		'title' => 'Clone',
		'fields' => [
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta3',
				'name'              => 'TA3 - Clone',
				'clone'             => true,
				'multiple'          => false,
				'clone_as_multiple' => false,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta4',
				'name'              => 'TA4 - Clone + Clone as multiple',
				'clone'             => true,
				'multiple'          => false,
				'clone_as_multiple' => true,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta5',
				'name'              => 'TA5 - Clone + Multiple',
				'clone'             => true,
				'multiple'          => true,
				'clone_as_multiple' => false,
			],
			[
				'type'              => 'taxonomy_advanced',
				'id'                => 'ta6',
				'name'              => 'TA6 - Clone + Multiple + Clone as multiple',
				'clone'             => true,
				'multiple'          => true,
				'clone_as_multiple' => true,
			],
		],
	];
	return $meta_boxes;
} );