<?php
/**
 * This file demonstrates how to use 'text' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_text_demo' );
function your_prefix_text_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'Text Demo', 'your-prefix' ),

		//'pages' => array('page'),
		//Restrict metaboxes in a specific Page Template
		//'show_on' => array('key' => 'page-template', 'value' => array('pageTemplate1.php','pageTemplate2.php')),
    //Restrict metaboxes in Pages without Template
		//'contentpage' => true,

		'fields' => array(
			array(
				'name'        => __( 'Text', 'your-prefix' ),
				'label_description' => __( 'Label description', 'your' ),
				'id'          => 'text',
				'desc'        => __( 'Text description', 'your-prefix' ),
				'type'        => 'text',

				// Default value (optional)
				'std'         => __( 'Default text value', 'your-prefix' ),

				// CLONES: Add to make the field cloneable (i.e. have multiple value)
				'clone'       => true,

				// Placeholder
				'placeholder' => __( 'Enter something here', 'your-prefix' ),

				// Input size
				'size'        => 30,

				// Datalist
				'datalist'    => array(
					// Unique ID for datalist
					'id'      => 'text_datalist',
					// List of predefined options
					'options' => array(
						__( 'What', 'your-prefix' ),
						__( 'When', 'your-prefix' ),
						__( 'Where', 'your-prefix' ),
						__( 'Why', 'your-prefix' ),
						__( 'Who', 'your-prefix' ),
					),
				),
			),
		),
	);
	return $meta_boxes;
}
