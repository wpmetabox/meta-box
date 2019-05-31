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
		RWMB_Image_Field::admin_enqueue_scripts();
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
		$field              = wp_parse_args(
			$field,
			array(
				'image_size' => 'thumbnail',
			)
		);

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args(
			$field['js_options'],
			array(
				'imageSize' => $field['image_size'],
			)
		);

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
	 * @param int   $file  Attachment image ID (post ID). Required.
	 * @param array $args  Array of arguments (for size).
	 * @param array $field Field settings.
	 *
	 * @return array|bool False if file not found. Array of image info on success.
	 */
	public static function file_info( $file, $args = array(), $field = array() ) {
		return RWMB_Image_Field::file_info( $file, $args, $field );
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return RWMB_Image_Field::format_single_value( $field, $value, $args, $post_id );
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		require_once RWMB_INC_DIR . 'templates/image-advanced.php';
	}
}
