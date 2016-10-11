<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title' => 'Test date time fields',

		'fields' => [
			[
				'name' => 'Date',
				'id'   => 'date',
				'type' => 'date',

				'js_options' => [
					'appendText'      => '(yyyy-mm-dd]',
					'autoSize'        => true,
					'buttonText'      => 'Select Date',
					'dateFormat'      => 'yy-mm-dd',
					'numberOfMonths'  => 2,
					'showButtonPanel' => true,
				],
				'timestamp' => true,
			],
			[
				'name' => 'Date 2',
				'id'   => 'date2',
				'type' => 'date',
			],
			// Test if date picker is shown above the editor
			[
				'type' => 'wysiwyg',
				'id'   => 'wysiwyg',
			],
			// Inline mode
			[
				'name'   => 'Inline Date',
				'id'     => 'inline-date',
				'type'   => 'date',
				'inline' => true,
			],
			// Timestamp
			[
				'name'      => 'Date time with timestamp',
				'type'      => 'datetime',
				'id'        => 'datetime',
				'timestamp' => true,
			],
		],
	];

	return $meta_boxes;
} );
