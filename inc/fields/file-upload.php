<?php
defined( 'ABSPATH' ) || die;

/**
 * The file upload field which allows users to drag and drop files to upload.
 */
class RWMB_File_Upload_Field extends RWMB_Media_Field {
	private static string $upload_dir = '';

	/**
	 * Callback passed to wp_unique_filename() for this request.
	 *
	 * @var callable|null
	 */
	private static $filename_callback;

	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-upload', RWMB_CSS_URL . 'upload.css', [ 'rwmb-media' ], RWMB_VER );
		wp_style_add_data( 'rwmb-upload', 'path', RWMB_CSS_DIR . 'upload.css' );
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

		if ( $field['upload_dir'] ) {
			$field['js_options']['uploadDir'] = true;
			$field['js_options']['fieldId']   = $field['id'];
		}

		return $field;
	}

	/**
	 * Send drag-and-drop uploads to the field's upload_dir.
	 * The folder comes from the registered field, not from the request.
	 *
	 * @param array $file A single $_FILES item.
	 */
	public static function filter_upload( array $file ): array {
		$field_id    = sanitize_text_field( (string) rwmb_request()->post( 'rwmb_field_id', '' ) );
		$meta_box_id = sanitize_text_field( (string) rwmb_request()->post( 'rwmb_meta_box_id', '' ) );
		$field       = $field_id && $meta_box_id ? self::find_upload_field( $meta_box_id, $field_id ) : null;
		$dir         = is_array( $field ) ? $field['upload_dir'] : '';

		if ( $dir && RWMB_File_Field::is_path_inside( $dir, self::uploads_basedir() ) ) {
			self::$upload_dir = $dir;
			add_filter( 'upload_dir', [ __CLASS__, 'custom_upload_dir' ] );
			if ( is_callable( $field['unique_filename_callback'] ) ) {
				self::$filename_callback = $field['unique_filename_callback'];
				add_filter( 'wp_handle_upload_overrides', [ __CLASS__, 'upload_overrides' ] );
			}
			return $file;
		}

		if ( $field_id && $meta_box_id ) {
			$file['error'] = __( 'The upload folder must be inside the WordPress uploads directory.', 'meta-box' );
		}

		return $file;
	}

	/**
	 * Callback for the upload_dir filter during a drag-and-drop upload.
	 *
	 * @param array $uploads Value from the upload_dir filter.
	 */
	public static function custom_upload_dir( array $uploads ): array {
		// _wp_handle_upload() reads this once, then keeps the returned path and url.
		remove_filter( 'upload_dir', [ __CLASS__, 'custom_upload_dir' ] );
		remove_filter( 'wp_handle_upload_overrides', [ __CLASS__, 'upload_overrides' ] );

		return RWMB_File_Field::filter_custom_upload_dir( $uploads, self::$upload_dir );
	}

	/**
	 * Pass the field's filename callback through to wp_handle_upload().
	 *
	 * @param array|false $overrides Upload overrides.
	 * @return array
	 */
	public static function upload_overrides( $overrides ): array {
		$overrides                             = is_array( $overrides ) ? $overrides : [];
		$overrides['unique_filename_callback'] = self::$filename_callback;

		return $overrides;
	}

	private static function find_upload_field( string $meta_box_id, string $field_id ): ?array {
		$meta_box = rwmb_get_registry( 'meta_box' )->get( $meta_box_id );
		$field    = $meta_box ? self::search_fields( $meta_box->fields, $field_id ) : null;

		return $field ? RWMB_Field::call( 'normalize', $field ) : null;
	}

	private static function search_fields( array $fields, string $field_id ): ?array {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$type = $field['type'] ?? '';
			if ( ( $field['id'] ?? '' ) === $field_id && in_array( $type, [ 'file_upload', 'image_upload', 'plupload_image' ], true ) ) {
				return $field;
			}

			if ( ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				$found = self::search_fields( $field['fields'], $field_id );
				if ( $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	private static function uploads_basedir(): string {
		$uploads = wp_get_upload_dir();

		return $uploads['error'] ? '' : $uploads['basedir'];
	}
}
