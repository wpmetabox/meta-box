<?php
defined( 'ABSPATH' ) || die;

/**
 * The image upload field which allows users to drag and drop images.
 */
class RWMB_Image_Upload_Field extends RWMB_Image_Advanced_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		RWMB_File_Upload_Field::admin_enqueue_scripts();
		wp_enqueue_script( 'rwmb-image-upload', RWMB_JS_URL . 'image-upload.js', [ 'rwmb-file-upload', 'rwmb-image-advanced' ], RWMB_VER, true );
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
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 */
	public static function html( $meta, $field ) {
		return parent::html( $meta, RWMB_File_Upload_Field::prepare_upload_dir( $field ) );
	}
}
