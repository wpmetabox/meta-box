<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class RWMB_Image_Upload_Field extends RWMB_Image_Advanced_Field
{
	/**
	 * Add actions
	 *
	 * @return void
	 */
	static function add_actions()
	{
		parent::add_actions();
		// Print attachment templates
		add_action( 'print_media_templates', array( 'RWMB_File_Upload_Field', 'print_templates' ) );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		RWMB_File_Upload_Field::admin_enqueue_scripts();
		wp_enqueue_script( 'rwmb-image-upload', RWMB_JS_URL . 'image-upload.js', array( 'rwmb-file-upload', 'rwmb-image-advanced' ), RWMB_VER, true );
	}
}
