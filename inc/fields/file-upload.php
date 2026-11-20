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
		return parent::html( $meta, self::prepare_upload_dir( $field ) );
	}

	/**
	 * JS options are set here, after normalize filters may have changed upload_dir.
	 * Cloned inputs keep one nonce: the id is the original id, and clone indexes are left out of the field name.
	 */
	public static function prepare_upload_dir( array $field ): array {
		if ( ! ( $field['upload_dir'] ?? '' ) ) {
			return $field;
		}

		$field_id    = (string) ( $field['_original_id'] ?? $field['id'] );
		$field_name  = (string) ( $field['field_name'] ?? $field_id );
		$meta_box_id = (string) ( $field['meta_box_id'] ?? '' );

		$field['js_options']['uploadDir']      = true;
		$field['js_options']['fieldId']        = $field_id;
		$field['js_options']['metaBoxId']      = $meta_box_id;
		$field['js_options']['uploadDirNonce'] = wp_create_nonce( self::upload_nonce_action( $meta_box_id, $field_id, $field_name ) );

		return $field;
	}

	public static function upload_nonce_action( string $meta_box_id, string $field_id, string $field_name ): string {
		return 'rwmb-upload-dir|' . $meta_box_id . '|' . $field_id . '|' . self::nonce_field_name( $field_name );
	}

	private static function nonce_field_name( string $field_name ): string {
		// clone.js rewrites indexes after render, so docs[0][] and docs[1][] share one nonce.
		$field_name = preg_replace( '/\[\d+\]/', '', $field_name ) ?? $field_name;

		return str_ends_with( $field_name, '[]' ) ? $field_name : $field_name . '[]';
	}

	/**
	 * Point this request at the field folder stored in the registry.
	 * The filters stay until the request ends, so a later wp_upload_dir() still uses that folder.
	 *
	 * @param array $file A single $_FILES item.
	 */
	public static function filter_upload( array $file ): array {
		$field_id    = sanitize_text_field( (string) rwmb_request()->post( 'rwmb_field_id', '' ) );
		$meta_box_id = sanitize_text_field( (string) rwmb_request()->post( 'rwmb_meta_box_id', '' ) );
		$field_name  = sanitize_text_field( (string) rwmb_request()->post( 'rwmb_field_name', '' ) );
		if ( ! $field_id || ! $meta_box_id ) {
			return $file;
		}

		$field = $field_name ? self::find_upload_field( $meta_box_id, $field_id, $field_name ) : null;
		if ( ! $field ) {
			$file['error'] = __( 'The field for this upload could not be found.', 'meta-box' );

			return $file;
		}

		$dir = $field['upload_dir'] ?? '';
		if ( ! $dir ) {
			return $file;
		}

		if ( ! RWMB_File_Field::is_path_inside( $dir, self::uploads_basedir() ) ) {
			$file['error'] = __( 'The upload folder must be inside the WordPress uploads directory.', 'meta-box' );

			return $file;
		}

		$nonce = (string) rwmb_request()->post( 'rwmb_upload_dir_nonce', '' );
		if ( ! wp_verify_nonce( $nonce, self::upload_nonce_action( $meta_box_id, $field_id, $field_name ) ) ) {
			$file['error'] = __( 'You are not allowed to upload to this folder.', 'meta-box' );

			return $file;
		}

		add_filter( 'upload_dir', fn( $uploads ) => RWMB_File_Field::filter_custom_upload_dir( $uploads, $dir ) );

		$callback = $field['unique_filename_callback'] ?? null;
		if ( is_callable( $callback ) ) {
			// wp_handle_sideload reads a different overrides hook than wp_handle_upload.
			$overrides = fn( $overrides ) => array_merge( $overrides ?: [], [
				'unique_filename_callback' => $callback,
			] );
			add_filter( 'wp_handle_upload_overrides', $overrides );
			add_filter( 'wp_handle_sideload_overrides', $overrides );
		}

		return $file;
	}

	private static function find_upload_field( string $meta_box_id, string $field_id, string $field_name ): ?array {
		$meta_box = rwmb_get_registry( 'meta_box' )->get( $meta_box_id );
		if ( ! $meta_box ) {
			return null;
		}

		$ids     = self::name_ids( $field_name );
		$by_name = self::find_by_path( $meta_box->fields, $ids );
		if ( ( $by_name['id'] ?? '' ) === $field_id ) {
			return $by_name;
		}

		// A nested name that missed its parent must not use another field with the same id.
		return count( $ids ) < 2 ? self::search_fields( $meta_box->fields, $field_id ) : null;
	}

	private static function name_ids( string $field_name ): array {
		// Media modal prefixes the input with attachments[post_id].
		$field_name = preg_replace( '/^attachments\[\d+\]/', '', $field_name ) ?? $field_name;
		preg_match_all( '/([^\[\]]+)/', $field_name, $matches );

		return array_filter( $matches[1] ?? [], fn( $part ) => $part && ! is_numeric( $part ) );
	}

	private static function matches_segment( array $field, string $segment ): bool {
		$name = (string) ( $field['field_name'] ?? '' );
		$name = strstr( $name, '[', true ) ?: $name;

		return ( $field['id'] ?? '' ) === $segment || $name === $segment;
	}

	private static function find_by_path( array $fields, array $ids ): ?array {
		if ( ! $ids ) {
			return null;
		}

		$id = (string) array_shift( $ids );
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) || ! self::matches_segment( $field, $id ) ) {
				continue;
			}

			if ( $ids ) {
				return is_array( $field['fields'] ?? null ) ? self::find_by_path( $field['fields'], $ids ) : null;
			}

			return self::is_upload_field( $field ) ? $field : null;
		}

		return null;
	}

	private static function search_fields( array $fields, string $field_id ): ?array {
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			if ( ( $field['id'] ?? '' ) === $field_id && self::is_upload_field( $field ) ) {
				return $field;
			}

			if ( is_array( $field['fields'] ?? null ) ) {
				$found = self::search_fields( $field['fields'], $field_id );
				if ( $found ) {
					return $found;
				}
			}
		}

		return null;
	}

	private static function is_upload_field( array $field ): bool {
		return in_array( $field['type'] ?? '', [ 'file_upload', 'image_upload', 'plupload_image' ], true );
	}

	private static function uploads_basedir(): string {
		// wp_get_upload_dir() already applies upload_dir, so another plugin's basedir is included once.
		$uploads = wp_get_upload_dir();

		return $uploads['error'] ? '' : $uploads['basedir'];
	}
}
