<?php
/**
 * This file tests uploading files to custom folder (new in Meta Box 4.16.0).
 */

add_filter(
	'rwmb_meta_boxes',
	function( $meta_boxes ) {
		$meta_boxes[] = [
			'title'  => 'File Upload Custom Folder',
			'fields' => [
				[
					'type'         => 'file',
					'id'           => 'f',
					'name'         => 'Upload Directory In Root',
					'upload_dir'   => ABSPATH . 'uploads',
					'force_delete' => true,
				],
				[
					'type'       => 'file',
					'id'         => 'f2',
					'name'       => 'Upload Directory In wp-content',
					'upload_dir' => WP_CONTENT_DIR . '/custom',
				],
				[
					'type'       => 'file',
					'id'         => 'f3',
					'name'       => 'Upload Directory In wp-content/uploads',
					'upload_dir' => WP_CONTENT_DIR . '/uploads/files',
				],
				[
					'type' => 'file',
					'id'   => 'f4',
					'name' => 'Normal Upload',
				],
				[
					'type'         => 'file',
					'id'           => 'f5',
					'name'         => 'Upload to upper folder',
					'upload_dir'   => '../',
				],
			],
		];
		return $meta_boxes;
	}
);

add_filter(
	'the_content',
	function( $content ) {
		if ( ! is_single() ) {
			return $content;
		}
		$value    = rwmb_meta( 'f' );
		$value    = '<pre>' . print_r( $value, true ) . '</pre>';
		$content .= $value;

		return $content;
	}
);
