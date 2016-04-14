<?php
/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class RWMB_File_Upload_Field extends RWMB_File_Advanced_Field
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
		add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-upload', RWMB_CSS_URL . 'upload.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-file-upload', RWMB_JS_URL . 'file-upload.js', array( 'rwmb-media' ), RWMB_VER, true );
	}

	/**
	 * Template for media item
	 * @return void
	 */
	static function print_templates()
	{
		require_once( RWMB_INC_DIR . 'templates/upload.php' );
	}
}
