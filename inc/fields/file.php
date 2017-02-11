<?php
/**
 * The file upload file which allows users to upload files via the default HTML <input type="file">.
 *
 * @package Meta Box
 */

/**
 * File field class which uses HTML <input type="file"> to upload file.
 */
class RWMB_File_Field extends RWMB_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-file', RWMB_CSS_URL . 'file.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-file', RWMB_JS_URL . 'file.js', array( 'jquery' ), RWMB_VER, true );

		self::localize_script( 'rwmb-file', 'rwmbFile', array(
			// Translators: %d is the number of files in singular form.
			'maxFileUploadsSingle' => __( 'You may only upload maximum %d file', 'meta-box' ),
			// Translators: %d is the number of files in plural form.
			'maxFileUploadsPlural' => __( 'You may only upload maximum %d files', 'meta-box' ),
		) );
	}

	/**
	 * Add custom actions.
	 */
	public static function add_actions() {
		add_action( 'post_edit_form_tag', array( __CLASS__, 'post_edit_form_tag' ) );
		add_action( 'wp_ajax_rwmb_delete_file', array( __CLASS__, 'wp_ajax_delete_file' ) );
		add_action( 'wp_ajax_rwmb_reorder_files', array( __CLASS__, 'wp_ajax_reorder_files' ) );
	}

	/**
	 * Add data encoding type for file uploading
	 */
	public static function post_edit_form_tag() {
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Ajax callback for reordering images
	 */
	public static function wp_ajax_reorder_files() {
		$post_id  = (int) filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$field_id = (string) filter_input( INPUT_POST, 'field_id' );
		$order    = (string) filter_input( INPUT_POST, 'order' );

		check_ajax_referer( "rwmb-reorder-files_{$field_id}" );
		parse_str( $order, $items );
		delete_post_meta( $post_id, $field_id );
		foreach ( $items['item'] as $item ) {
			add_post_meta( $post_id, $field_id, $item, false );
		}
		wp_send_json_success();
	}

	/**
	 * Ajax callback for deleting files.
	 * Modified from a function used by "Verve Meta Boxes" plugin.
	 *
	 * @link http://goo.gl/LzYSq
	 */
	public static function wp_ajax_delete_file() {
		$post_id       = (int) filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$field_id      = (string) filter_input( INPUT_POST, 'field_id' );
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );
		$force_delete  = (int) filter_input( INPUT_POST, 'force_delete', FILTER_SANITIZE_NUMBER_INT );

		check_ajax_referer( "rwmb-delete-file_{$field_id}" );
		delete_post_meta( $post_id, $field_id, $attachment_id );
		$success = $force_delete ? wp_delete_attachment( $attachment_id ) : true;

		if ( $success ) {
			wp_send_json_success();
		}
		wp_send_json_error( __( 'Error: Cannot delete file', 'meta-box' ) );
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
		$i18n_title = apply_filters( 'rwmb_file_upload_string', _x( 'Upload Files', 'file upload', 'meta-box' ), $field );
		$i18n_more  = apply_filters( 'rwmb_file_add_string', _x( '+ Add new file', 'file upload', 'meta-box' ), $field );

		// Uploaded files.
		$html    = self::get_uploaded_files( $meta, $field );
		$classes = 'new-files';
		if ( ! empty( $field['max_file_uploads'] ) && count( $meta ) >= (int) $field['max_file_uploads'] ) {
			$classes .= ' hidden';
		}

		// Show form upload.
		$html .= sprintf(
			'<div class="%s">
				<h4>%s</h4>
				<div class="file-input"><input type="file" name="%s[]" /></div>
				<a class="rwmb-add-file" href="#"><strong>%s</strong></a>
			</div>',
			$classes,
			$i18n_title,
			$field['id'],
			$i18n_more
		);

		return $html;
	}

	/**
	 * Get HTML for uploaded files.
	 *
	 * @param array $files List of uploaded files.
	 * @param array $field Field parameters.
	 * @return string
	 */
	protected static function get_uploaded_files( $files, $field ) {
		$reorder_nonce = wp_create_nonce( "rwmb-reorder-files_{$field['id']}" );
		$delete_nonce  = wp_create_nonce( "rwmb-delete-file_{$field['id']}" );

		$classes = 'rwmb-uploaded';
		if ( count( $files ) <= 0 ) {
			$classes .= ' hidden';
		}

		foreach ( (array) $files as $k => $file ) {
			$files[ $k ] = self::call( $field, 'file_html', $file );
		}
		return sprintf(
			'<ul class="%s" data-field_id="%s" data-delete_nonce="%s" data-reorder_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s" data-mime_type="%s">%s</ul>',
			$classes,
			$field['id'],
			$delete_nonce,
			$reorder_nonce,
			$field['force_delete'] ? 1 : 0,
			$field['max_file_uploads'],
			$field['mime_type'],
			implode( '', $files )
		);
	}

	/**
	 * Get HTML for uploaded file.
	 *
	 * @param int $file Attachment (file) ID.
	 * @return string
	 */
	protected static function file_html( $file ) {
		$i18n_delete = apply_filters( 'rwmb_file_delete_string', _x( 'Delete', 'file upload', 'meta-box' ) );
		$i18n_edit   = apply_filters( 'rwmb_file_edit_string', _x( 'Edit', 'file upload', 'meta-box' ) );
		$mime_type   = get_post_mime_type( $file );

		return sprintf(
			'<li id="item_%s">
				<div class="rwmb-icon">%s</div>
				<div class="rwmb-info">
					<a href="%s" target="_blank">%s</a>
					<p>%s</p>
					<a href="%s" target="_blank">%s</a> |
					<a class="rwmb-delete-file" href="#" data-attachment_id="%s">%s</a>
				</div>
			</li>',
			$file,
			wp_get_attachment_image( $file, array( 60, 60 ), true ),
			wp_get_attachment_url( $file ),
			get_the_title( $file ),
			$mime_type,
			get_edit_post_link( $file ),
			$i18n_edit,
			$file,
			$i18n_delete
		);
	}

	/**
	 * Get meta values to save.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return array|mixed
	 */
	public static function value( $new, $old, $post_id, $field ) {
		// @codingStandardsIgnoreLine
		if ( empty( $_FILES[ $field['id'] ] ) ) {
			return $new;
		}

		$new   = array();
		// @codingStandardsIgnoreLine
		$files = self::transform( $_FILES[ $field['id'] ] );
		foreach ( $files as $file ) {
			$new[] = self::upload( $file, $post_id );
		}

		return array_filter( array_unique( array_merge( (array) $old, $new ) ) );
	}

	/**
	 * Handle upload file.
	 *
	 * @param array $file Uploaded file info.
	 * @param int   $post Post parent ID.
	 * @return int Attachment ID on success, false on failure.
	 */
	protected static function upload( $file, $post ) {
		$file = wp_handle_upload( $file, array(
			'test_form' => false,
		) );
		if ( ! isset( $file['file'] ) ) {
			return false;
		}

		$attachment = wp_insert_attachment( array(
			'post_mime_type' => $file['type'],
			'guid'           => $file['url'],
			'post_parent'    => $post,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file['file'] ) ),
			'post_content'   => '',
		), $file['file'], $post );
		if ( is_wp_error( $attachment ) || ! $attachment ) {
			return false;
		}
		wp_update_attachment_metadata( $attachment, wp_generate_attachment_metadata( $attachment, $file['file'] ) );
		return $attachment;
	}

	/**
	 * Transform $_FILES from $_FILES['field']['key']['index'] to $_FILES['field']['index']['key'].
	 *
	 * @param array $files Uploaded files info.
	 * @return array
	 */
	protected static function transform( $files ) {
		$output = array();
		foreach ( $files as $key => $list ) {
			foreach ( $list as $index => $value ) {
				$output[ $index ][ $key ] = $value;
			}
		}

		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field             = parent::normalize( $field );
		$field             = wp_parse_args( $field, array(
			'std'              => array(),
			'force_delete'     => false,
			'max_file_uploads' => 0,
			'mime_type'        => '',
		) );
		$field['multiple'] = true;

		return $field;
	}

	/**
	 * Get the field value. Return meaningful info of the files.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Not used for this field.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Full info of uploaded files
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		$value = parent::get_value( $field, $args, $post_id );
		if ( ! $field['clone'] ) {
			$value = self::call( 'files_info', $field, $value, $args );
		} else {
			$return = array();
			foreach ( $value as $subvalue ) {
				$return[] = self::call( 'files_info', $field, $subvalue, $args );
			}
			$value = $return;
		}
		if ( isset( $args['limit'] ) ) {
			$value = array_slice( $value, 0, intval( $args['limit'] ) );
		}
		return $value;
	}

	/**
	 * Get uploaded files information.
	 *
	 * @param array $field Field parameters.
	 * @param array $files Files IDs.
	 * @param array $args  Additional arguments (for image size).
	 * @return array
	 */
	public static function files_info( $field, $files, $args ) {
		$return = array();
		foreach ( (array) $files as $file ) {
			$info = self::call( $field, 'file_info', $file, $args );
			if ( $info ) {
				$return[ $file ] = $info;
			}
		}
		return $return;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file Attachment file ID (post ID). Required.
	 * @param array $args Array of arguments (for size).
	 *
	 * @return array|bool False if file not found. Array of (id, name, path, url) on success.
	 */
	public static function file_info( $file, $args = array() ) {
		$path = get_attached_file( $file );
		if ( ! $path ) {
			return false;
		}

		return wp_parse_args( array(
			'ID'    => $file,
			'name'  => basename( $path ),
			'path'  => $path,
			'url'   => wp_get_attachment_url( $file ),
			'title' => get_the_title( $file ),
		), wp_get_attachment_metadata( $file ) );
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field Field parameters.
	 * @param string|array $value The field meta value.
	 * @return string
	 */
	public static function format_value( $field, $value ) {
		if ( ! $field['clone'] ) {
			return self::call( 'format_single_value', $field, $value );
		}
		$output = '<ul>';
		foreach ( $value as $subvalue ) {
			$output .= '<li>' . self::call( 'format_single_value', $field, $subvalue ) . '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions.
	 *
	 * @param array $field Field parameters.
	 * @param array $value The value.
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		$output = '<ul>';
		foreach ( $value as $file ) {
			$output .= sprintf( '<li><a href="%s" target="_blank">%s</a></li>', $file['url'], $file['title'] );
		}
		$output .= '</ul>';
		return $output;
	}
}
