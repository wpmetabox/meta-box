<?php
/**
 * This file demonstrates how to use 'file' field
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_file_demo' );
function your_prefix_file_demo( $meta_boxes )
{
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
		),
	);
	return $meta_boxes;
}
