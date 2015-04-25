<?php
/**
 * This file demonstrates how to use 'key_value' field
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_key_value_demo' );
function your_prefix_key_value_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Key Value Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'   => 'key_value',
				'name' => __( 'Key Value', 'your-prefix' ),
				'type' => 'key_value',

				'desc' => __( 'Add more additional info below:', 'your-prefix' ),
			),
		),
	);
	return $meta_boxes;
}
