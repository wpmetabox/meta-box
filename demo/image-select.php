<?php
/**
 * This file demonstrates how to use 'image_select' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_image_select_demo' );
function your_prefix_image_select_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title' => __( 'Image Select Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'       => 'image_select',
				'name'     => __( 'Layout', 'your-prefix' ),
				'type'     => 'image_select',

				// Array of 'value' => 'Image Source' pairs
				'options'  => array(
					'left'  => 'http://placehold.it/90x90&text=Left',
					'right' => 'http://placehold.it/90x90&text=Right',
					'none'  => 'http://placehold.it/90x90&text=None',
				),

				// Allow to select multiple values? Default is false
				// 'multiple' => true,
			),
		),
	);
	return $meta_boxes;
}
