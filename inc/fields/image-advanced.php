<?php
/**
 * The advanced image upload field which uses WordPress media popup to upload and select images.
 *
 * @package Meta Box
 */

/**
 * Image advanced field class.
 */
class RWMB_Image_Advanced_Field extends RWMB_Media_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-image-advanced', RWMB_CSS_URL . 'image-advanced.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-image-advanced', RWMB_JS_URL . 'image-advanced.js', array( 'rwmb-media' ), RWMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field['mime_type'] = 'image';
		$field              = wp_parse_args( $field, array(
			'image_size' => 'thumbnail',
		) );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], array(
			'imageSize' => $field['image_size'],
		) );

		return $field;
	}

	/**
	 * Get the field value.
	 *
	 * @param array $field   Field parameters.
	 * @param array $args    Additional arguments.
	 * @param null  $post_id Post ID.
	 * @return mixed
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		return RWMB_Image_Field::get_value( $field, $args, $post_id );
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file Attachment image ID (post ID). Required.
	 * @param array $args Array of arguments (for size).
	 * @return array|bool False if file not found. Array of image info on success.
	 */
	public static function file_info( $file, $args = array() ) {
		return RWMB_Image_Field::file_info( $file, $args );
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field Field parameters.
	 * @param string|array $value The field meta value.
	 * @return string
	 */
	public static function format_value( $field, $value ) {
		return RWMB_Image_Field::format_value( $field, $value );
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array $field Field parameters.
	 * @param array $value The value.
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		return RWMB_Image_Field::format_single_value( $field, $value );
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		require_once RWMB_INC_DIR . 'templates/image-advanced.php';
	}
}
