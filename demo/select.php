<?php
/**
 * This file demonstrates how to use 'select' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_select_demo' );
function your_prefix_select_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title' => __( 'Select Field Demo', 'your-prefix' ),

		'fields' => array(
			array(
				'name'    => __( 'Select', 'your-prefix' ),
				'id'      => 'select_simple',
				'type'    => 'select',
				'options' => array(
					'value1' => __( 'Label1', 'your-prefix' ),
					'value2' => __( 'Label2', 'your-prefix' ),
				),
			),
			array(
				'name' => __( 'Select', 'your-prefix' ),
				'id'   => 'select',
				'type' => 'select',

				'clone'       => true,

				// Array of 'value' => 'Label' pairs for select box
				'options'     => array(
					'value1' => __( 'Label1', 'your-prefix' ),
					'value2' => __( 'Label2', 'your-prefix' ),
				),

				// Select multiple values, optional. Default is false.
				'multiple'    => true,

				// Default selected value
				'std'         => 'value2',

				// Placeholder
				'placeholder' => __( 'Select an Item', 'your-prefix' ),
			),
			array(
				'name'     => __( 'Select Advanced', 'your-prefix' ),
				'id'       => 'select_advanced',
				'type'     => 'select_advanced',

				// Array of 'value' => 'Label' pairs for select box
				'options'  => array(
					'value1' => __( 'Label1', 'your-prefix' ),
					'value2' => __( 'Label2', 'your-prefix' ),
				),

				// Select multiple values, optional. Default is false.
				'multiple' => false,

				'std'         => 'value2', // Default value, optional
				'placeholder' => __( 'Select an Item', 'your-prefix' ),
			),

		),
	);

	return $meta_boxes;
}


