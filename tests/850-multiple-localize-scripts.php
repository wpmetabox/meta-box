<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title'  => 'Test multiple localized scripts for image advanced',
		'fields' => [
			[
				'id'   => 'img1',
				'type' => 'image_advanced',
				'name' => 'Image 1',
			],
			[
				'id'   => 'img2',
				'type' => 'image_advanced',
				'name' => 'Image 2',
			],
			[
				'id'   => 'datetime1',
				'type' => 'datetime',
				'name' => 'Datetime 1',
			],
			[
				'id'   => 'datetime2',
				'type' => 'datetime',
				'name' => 'Datetime 2',
			],
		],
	];
	return $meta_boxes;
} );
