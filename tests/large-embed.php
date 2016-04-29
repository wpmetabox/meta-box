<?php
/**
 * This file tests the oembed video with very large dimensions.
 * The width of the embed video is set by global $content_width if no 'width' specified when calling the function to
 * render oembed. It's fine in the frontend, but might break the layout in the admin area. Due to $content_width is
 * made for frontend, we have to set a width 360px (a default value which looks good at screen 1024x768).
 *
 * @link https://github.com/rilwis/meta-box/issues/801
 */

// Overwrite global $content_width with large value
add_action( 'init', function ()
{
	$GLOBALS['content_width'] = 1920;
} );

// Register meta box with oembed field
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes )
{
	$meta_boxes[] = [
		'title'  => 'Test large oembed video',
		'fields' => [
			[
				'id'   => 'youtube',
				'name' => 'Youtube',
				'type' => 'oembed',
			],
		],
	];
	return $meta_boxes;
} );
