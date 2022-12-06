<?php
/**
 * The file upload field which allows users to drag and drop files to upload.
 */
class RWMB_File_Upload_Field extends RWMB_Media_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-upload', RWMB_CSS_URL . 'upload.css', [ 'rwmb-media' ], RWMB_VER );
		wp_enqueue_script( 'rwmb-file-upload', RWMB_JS_URL . 'file-upload.js', [ 'rwmb-media' ], RWMB_VER, true );
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
		$field = wp_parse_args( $field, [
			'max_file_size' => 0,
		] );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'maxFileSize' => $field['max_file_size'],
		] );

		return $field;
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		require_once RWMB_INC_DIR . 'templates/upload.php';
	}
}
