<?php
/**
 * The image upload field which allows users to drag and drop images.
 *
 * @package Meta Box
 */

/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class RWMB_Image_Upload_Field extends RWMB_Image_Advanced_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		RWMB_File_Upload_Field::admin_enqueue_scripts();
		wp_enqueue_script( 'rwmb-image-upload', RWMB_JS_URL . 'image-upload.js', array( 'rwmb-file-upload', 'rwmb-image-advanced' ), RWMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		return RWMB_File_Upload_Field::normalize( $field );
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		RWMB_File_Upload_Field::print_templates();
	}
}
