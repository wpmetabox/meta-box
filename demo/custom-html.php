<?php
/**
 * This file demonstrates how to use 'custom_html' field
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_custom_html_demo' );
function your_prefix_custom_html_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'Custom HTML Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'   => 'custom_html',

				// Field name: usually not used
				// 'name' => __( 'Custom HTML', 'your-prefix' ),
				'type' => 'custom_html',

				// HTML content
				'std'  => '<div class="warning">Please be careful with the data entered in each field</div>',

				// Callback function to show custom HTML
				// 'callback' => 'display_warning',
			),
		),
	);
	return $meta_boxes;
}
