<?php
/**
 * This file demonstrates how to use 'text_list' field
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_text_list_demo' );
function your_prefix_text_list_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Text List Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'      => 'text_list',
				'name'    => __( 'Text List', 'your-prefix' ),
				'type'    => 'text_list',

				'clone' => true,

				// Options: array of Placeholder => Label for text boxes
				// Number of options are not limited
				'options' => array(
					'John Smith'      => __( 'Name', 'your-prefix' ),
					'name@domain.com' => __( 'Email', 'your-prefix' ),
				),
			),
		),
	);
	return $meta_boxes;
}
