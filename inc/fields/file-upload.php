<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class RWMB_File_Upload_Field extends RWMB_Media_Field
{
	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-upload', RWMB_CSS_URL . 'upload.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-file-upload', RWMB_JS_URL . 'file-upload.js', array( 'rwmb-media' ), RWMB_VER, true );
	}

	/**
	 * Template for media item
	 */
	public static function print_templates()
	{
		parent::print_templates();
		require_once RWMB_INC_DIR . 'templates/upload.php';
	}
}
