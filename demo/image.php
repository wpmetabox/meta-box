<?php
/**
 * This image demonstrates how to use 'image' fields
 */

add_filter( 'rwmb_meta_boxes', 'your_prefix_image_demo' );
function your_prefix_image_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Image Upload Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'               => 'image',
				'name'             => __( 'Image', 'your-prefix' ),
				'type'             => 'image',

				// Delete image from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same image for multiple posts
				'force_delete'     => false,

				// Maximum image uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'               => 'image_advanced',
				'name'             => __( 'Image Advanced', 'your-prefix' ),
				'type'             => 'image_advanced',

				// Delete image from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same image for multiple posts
				'force_delete'     => false,

				// Maximum image uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'               => 'plupload_image',
				'name'             => __( 'Plupload Image', 'your-prefix' ),
				'type'             => 'plupload_image',

				// Delete image from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same image for multiple posts
				'force_delete'     => false,

				// Maximum image uploads
				'max_file_uploads' => 2,
			),
			array(
				'id'           => 'thickbox_image',
				'name'         => __( 'Thickbox Image', 'your-prefix' ),
				'type'         => 'thickbox_image',

				// Delete image from Media Library when remove it from post meta?
				// Note: it might affect other posts if you use same image for multiple posts
				'force_delete' => false,
			),
		),
	);
	return $meta_boxes;
}
