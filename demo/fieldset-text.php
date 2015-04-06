<?php
/**
 * This file demonstrates how to use 'fieldset_text' field
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_fieldset_text_demo' );
function your_prefix_fieldset_text_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Fieldset Text Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'      => 'fieldset_text',
				'name'    => __( 'Fieldset Text', 'your-prefix' ),
				'type'    => 'fieldset_text',

				'desc'    => __( 'Please enter following details:', 'your-prefix' ),

				// Number of rows
				'rows'    => 3,

				// Options: array of Label => key for text boxes
				// Note: key is used as key of array of values stored in the database
				// Number of options are not limited
				'options' => array(
					'name'    => __( 'Name', 'your-prefix' ),
					'address' => __( 'Address', 'your-prefix' ),
					'email'   => __( 'Email', 'your-prefix' ),
				),
			),
		),
	);
	return $meta_boxes;
}
