<?php
/**
 * This file demonstrates how to use 'radio' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_radio_demo' );
function your_prefix_radio_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'Radio Field Demo', 'your-prefix' ),

		'fields' => array(
			array(
				'name'    => __( 'Radio', 'your-prefix' ),
				'id'      => 'radio',
				'type'    => 'radio',

				// Array of 'value' => 'Label' pairs for radio options.
				// Note: the 'value' is stored in meta field, not the 'Label'
				'options' => array(
					'value1' => __( 'Label1', 'your-prefix' ),
					'value2' => __( 'Label2', 'your-prefix' ),
				),
			),
		),
	);

	return $meta_boxes;
}


