<?php
/**
 * This file demonstrates how to use 'oembed' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_oembed_demo' );
function your_prefix_oembed_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'oEmbed Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'    => 'oembed',
				'name'  => __( 'oEmbed(s)', 'your-prefix' ),
				'type'  => 'oembed',

				// Allow to clone? Default is false
				'clone' => false,

				// Input size
				'size'  => 30,
			),
		),
	);
	return $meta_boxes;
}
