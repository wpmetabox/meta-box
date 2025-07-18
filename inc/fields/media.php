<?php
defined( 'ABSPATH' ) || die;

/**
 * Media field class which users WordPress media popup to upload and select files.
 */
class RWMB_Media_Field extends RWMB_File_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();

		wp_enqueue_media();
		if ( ! is_admin() ) {
			wp_register_script( 'media-grid', includes_url( 'js/media-grid.min.js' ), [ 'media-editor' ], '4.9.7', true );
		}
		wp_enqueue_style( 'rwmb-media', RWMB_CSS_URL . 'media.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-media', 'path', RWMB_CSS_DIR . 'media.css' );
		wp_enqueue_script( 'rwmb-media', RWMB_JS_URL . 'media.js', [ 'jquery-ui-sortable', 'underscore', 'backbone', 'media-grid' ], RWMB_VER, true );

		RWMB_Helpers_Field::localize_script_once( 'rwmb-media', 'i18nRwmbMedia', [
			'add'                => apply_filters( 'rwmb_media_add_string', _x( '+ Add Media', 'media', 'meta-box' ) ),
			'single'             => apply_filters( 'rwmb_media_single_files_string', _x( ' file', 'media', 'meta-box' ) ),
			'multiple'           => apply_filters( 'rwmb_media_multiple_files_string', _x( ' files', 'media', 'meta-box' ) ),
			'remove'             => apply_filters( 'rwmb_media_remove_string', _x( 'Remove', 'media', 'meta-box' ) ),
			'edit'               => apply_filters( 'rwmb_media_edit_string', _x( 'Edit', 'media', 'meta-box' ) ),
			'view'               => apply_filters( 'rwmb_media_view_string', _x( 'View', 'media', 'meta-box' ) ),
			'noTitle'            => _x( 'No Title', 'media', 'meta-box' ),
			'loadingUrl'         => admin_url( 'images/spinner.gif' ),
			'extensions'         => static::get_mime_extensions(),
			'select'             => apply_filters( 'rwmb_media_select_string', _x( 'Select Files', 'media', 'meta-box' ) ),
			'or'                 => apply_filters( 'rwmb_media_or_string', _x( 'or', 'media', 'meta-box' ) ),
			'uploadInstructions' => apply_filters( 'rwmb_media_upload_instructions_string', _x( 'Drop files here to upload', 'media', 'meta-box' ) ),
		] );
	}

	/**
	 * Get meta value.
	 *
	 * @param int   $post_id Post ID.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param array $field   Field parameters.
	 *
	 * @return mixed
	 */
	public static function meta( $post_id, $saved, $field ) {
		$meta = parent::meta( $post_id, $saved, $field );

		/*
		 * Update meta cache for all attachments, preparing for getting data for rendering in JS.
		 * This reduces the number of queries for updating all attachments' meta.
		 * @see get_attributes()
		 */
		$ids = (array) $meta;
		if ( $field['clone'] ) {
			foreach ( $ids as &$value ) {
				$value = (array) $value;
			}
			$ids = call_user_func_array( 'array_merge', $ids );
		}
		update_meta_cache( 'post', $ids );

		return $meta;
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = static::get_attributes( $field, $meta );

		$html = sprintf(
			'<input %s data-options="%s">',
			self::render_attributes( $attributes ),
			esc_attr( wp_json_encode( $field['js_options'] ) )
		);

		return $html;
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
			'std'              => [],
			'mime_type'        => '',
			'max_file_uploads' => 0,
			'force_delete'     => false,
			'max_status'       => true,
			'js_options'       => [],
			'add_to'           => 'end',
		] );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'mimeType'    => $field['mime_type'],
			'maxFiles'    => $field['max_file_uploads'],
			'forceDelete' => $field['force_delete'],
			'maxStatus'   => $field['max_status'],
			'addTo'       => $field['add_to'],
		] );

		$field['multiple'] = true;

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$value = (array) $value;

		$attributes           = parent::get_attributes( $field, $value );
		$attributes['type']   = 'hidden';
		$attributes['name']   = $field['clone'] ? str_replace( '[]', '', $attributes['name'] ) : $attributes['name'];
		$attributes['id']     = false;
		$attributes['value']  = implode( ',', $value );
		$attributes['class'] .= ' rwmb-media';

		// Add attachment details.
		$attachments                    = array_values( array_filter( array_map( 'wp_prepare_attachment_for_js', $value ) ) );
		$attributes['data-attachments'] = wp_json_encode( $attachments );

		if ( empty( $attachments ) ) {
			unset( $attributes['value'] );
		}

		return $attributes;
	}

	protected static function get_mime_extensions() : array {
		$mime_types = wp_get_mime_types();
		$extensions = [];
		foreach ( $mime_types as $ext => $mime ) {
			$ext                 = explode( '|', $ext );
			$extensions[ $mime ] = $ext;

			$mime_parts = explode( '/', $mime );
			if ( empty( $extensions[ $mime_parts[0] ] ) ) {
				$extensions[ $mime_parts[0] ] = [];
			}
			$extensions[ $mime_parts[0] ]        = array_merge( $extensions[ $mime_parts[0] ], $ext );
			$extensions[ $mime_parts[0] . '/*' ] = $extensions[ $mime_parts[0] ];
		}

		return $extensions;
	}

	/**
	 * Get meta values to save.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return array
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = wp_parse_id_list( $new );

		if ( empty( $new ) ) {
			return [];
		}

		// Attach the uploaded images to the post if needed.
		global $wpdb;
		$ids = implode( ',', $new );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent=%d WHERE post_parent=0 AND ID IN ($ids)", $post_id ) );

		return $new;
	}

	/**
	 * Save meta value.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 */
	public static function save( $new, $old, $post_id, $field ) {
		if ( empty( $field['id'] ) || ! $field['save_field'] ) {
			return;
		}
		$storage = $field['storage'];
		$storage->delete( $post_id, $field['id'] );
		parent::save( $new, [], $post_id, $field );
	}
}
