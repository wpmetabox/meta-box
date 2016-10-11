<?php
/**
 * This checkbox demonstrates how to use 'checkbox' fields
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_checkbox_demo' );
function your_prefix_checkbox_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'Checkbox Upload Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'   => 'checkbox',
				'name' => __( 'Checkbox', 'your-prefix' ),
				'type' => 'checkbox',
				'desc' => __( 'Check or not check?', 'your-prefix' ),
			),
		),
	);
	return $meta_boxes;
}
