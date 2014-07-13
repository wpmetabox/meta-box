<?php
add_filter( 'rwmb_meta_boxes', 'prefix_register_meta_box_image_select' );
function prefix_register_meta_box_image_select( $meta_boxes )
{
	$meta_boxes[] = array(
		'title' => __( 'Image Select Demo', 'textdomain' ),
		'fields' => array(
			array(
				'id'       => 'layout',
				'name'     => __( 'Layout', 'rwmb' ),
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