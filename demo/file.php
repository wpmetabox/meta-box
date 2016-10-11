<?php
/**
 * This file demonstrates how to use 'file' fields
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_file_demo' );
function your_prefix_file_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'File Upload Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'               => 'file',
				'name'             => __( 'File', 'your-prefix' ),
				'type'             => 'file',

				// Delete file from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same file for multiple posts
				'force_delete'     => false,

				// Maximum file uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'               => 'file_advanced',
				'name'             => __( 'File Advanced', 'your-prefix' ),
				'type'             => 'file_advanced',

				// Delete file from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same file for multiple posts
				'force_delete'     => false,

				// Maximum file uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'               => 'file_upload',
				'name'             => __( 'File Upload', 'your-prefix' ),
				'type'             => 'file_upload',

				// Delete file from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same file for multiple posts
				'force_delete'     => false,

				// Maximum file uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'          => 'file_input',
				'name'        => __( 'File Input', 'your-prefix' ),
				'type'        => 'file_input',

				// Input field placeholder
				'placeholder' => __( 'Please select a file or paste file URL here', 'your-prefix' ),

				// Input size
				'size'        => 60,
			),
		),
	);
	return $meta_boxes;
}
