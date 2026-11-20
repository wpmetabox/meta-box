<?php
defined( 'ABSPATH' ) || die;

/**
 * The file upload field which allows users to drag and drop files to upload.
 */
class RWMB_File_Upload_Field extends RWMB_Media_Field {
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

		return $field;
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 */
	public static function html( $meta, $field ) {
		return parent::html( $meta, self::sign_upload_dir( $field ) );
	}

	/**
	 * Sign the folder at render time, so a dynamic upload_dir works in the upload request.
	 * Only the part below the uploads folder is sent, never the server path.
	 */
	public static function sign_upload_dir( array $field ): array {
		$base = wp_normalize_path( wp_get_upload_dir()['basedir'] );
		$dir  = wp_normalize_path( untrailingslashit( (string) ( $field['upload_dir'] ?? '' ) ) );
		// A folder outside uploads, or one that climbs out with "..", is left unsigned.
		if ( str_starts_with( $dir, "$base/" ) && ! str_contains( $dir, '..' ) ) {
			$subdir                              = substr( $dir, strlen( $base ) );
			$field['js_options']['uploadSubdir'] = $subdir;
			$field['js_options']['uploadNonce']  = wp_create_nonce( "rwmb-upload-dir|$subdir" );
		}

		return $field;
	}

	/**
	 * Send the upload into the signed folder.
	 *
	 * @param array $file A single $_FILES item.
	 */
	public static function filter_upload( array $file ): array {
		$subdir = (string) rwmb_request()->post( 'rwmb_upload_subdir', '' );
		if ( ! $subdir ) {
			return $file;
		}

		if ( ! wp_verify_nonce( (string) rwmb_request()->post( 'rwmb_upload_nonce', '' ), "rwmb-upload-dir|$subdir" ) ) {
			$file['error'] = __( 'You are not allowed to upload to this folder.', 'meta-box' );

			return $file;
		}

		// Keep basedir, so attachment URLs and thumbnails stay valid. The filter lasts until the request ends.
		add_filter( 'upload_dir', fn( $uploads ) => array_merge( $uploads, [
			'subdir' => $subdir,
			'path'   => $uploads['basedir'] . $subdir,
			'url'    => $uploads['baseurl'] . $subdir,
		] ) );

		return $file;
	}
}
