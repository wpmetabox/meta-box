<?php
/**
 * This file tests 2 meta boxes registered for 2 different post types. The meta boxes have
 * the same field IDs, but different field params.
 * Result: The helper function must run correctly in the frontend.
 */

// Register 2 meta boxes with same field IDs but different field params
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes )
{
	$meta_boxes[] = [
		'title'  => 'Meta Box for Post',
		'fields' => [
			[
				'name' => 'Name',
				'id'   => 'name', // Same ID
				'type' => 'text',
			],
		],
	];
	$meta_boxes[] = [
		'title'      => 'Meta Box for Page',
		'post_types' => 'page',
		'fields'     => [
			[
				'name'    => 'Name',
				'id'      => 'name', // Same ID
				'type'    => 'select',
				'options' => [
					'asia'    => 'Asia',
					'europe'  => 'Europe',
					'america' => 'America',
				],
			],
		],
	];
	return $meta_boxes;
} );

// Output in the frontend
add_filter( 'the_content', function ( $content )
{
	$content .= '<h2>Test <code>rwmb_meta( \'name\' )</code></h2>' . rwmb_meta( 'name' );
	return $content;
} );
