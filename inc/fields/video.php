<?php
/**
 * Video class which users WordPress media popup to upload and select images.
 */
class RWMB_Video_Field extends RWMB_Media_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-video', RWMB_CSS_URL . 'video.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-video', RWMB_JS_URL . 'video.js', array( 'rwmb-media' ), RWMB_VER, true );
		self::localize_script( 'rwmb-video', 'i18nRwmbVideo', array(
			'extensions' => wp_get_video_extensions(),
		) );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field              = parent::normalize( $field );
		$field['mime_type'] = 'video';

		return $field;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file_id Attachment image ID (post ID). Required.
	 * @param array $args    Array of arguments (for size).
	 * @return array|bool False if file not found. Array of image info on success
	 */
	static function file_info( $file_id, $args = array() )
	{
		return;
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array $field Field parameter
	 * @param array $value The value
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		$ids = implode( ',', $value );
		return $ids;
		return wp_playlist_shortcode( array(
			'ids'  => $ids,
			'type' => 'video',
		));
	}

	/**
	 * Template for media item
	 * @return void
	 */
	static function print_templates()
	{
		parent::print_templates();
		require_once( RWMB_INC_DIR . 'templates/video.php' );
	}
}
