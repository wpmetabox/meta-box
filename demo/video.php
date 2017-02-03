<?php
/**
 * This file demonstrates how to use 'video' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_video_demo' );
function your_prefix_video_demo( $meta_boxes ) {
	$meta_boxes[] = array(
		'title' => esc_html__( 'Video Field Demo', 'your-prefix' ),

		'fields' => array(
			array(
				'name' => esc_html__( 'Video', 'your-prefix' ),
				'id'   => 'video',
				'type' => 'video',

				// Maximum video uploads. 0 = unlimited.
				'max_file_uploads' => 3,

				// Delete image from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same image for multiple posts
				'force_delete'     => false,

				// Display the "Uploaded 1/3 files" status
				'max_status'       => true,
			),
		),
	);

	return $meta_boxes;
}


