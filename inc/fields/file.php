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
		wp_enqueue_script( 'rwmb-file', RWMB_JS_URL . 'file.js', array( 'jquery-ui-sortable' ), RWMB_VER, true );

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
		add_action( 'wp_ajax_rwmb_delete_file', array( __CLASS__, 'ajax_delete_file' ) );
	}

	/**
	 * Add data encoding type for file uploading
	 */
	public static function post_edit_form_tag() {
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Ajax callback for deleting files.
	 */
	public static function ajax_delete_file() {
		$field_id      = (string) filter_input( INPUT_POST, 'field_id' );
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_SANITIZE_NUMBER_INT );

		check_ajax_referer( "rwmb-delete-file_{$field_id}" );
		if ( wp_delete_attachment( $attachment_id ) ) {
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
		$meta      = array_filter( (array) $meta );
		$i18n_more = apply_filters( 'rwmb_file_add_string', _x( '+ Add new file', 'file upload', 'meta-box' ), $field );
		$html      = self::get_uploaded_files( $meta, $field );

		// Show form upload.
		$html .= sprintf(
			'<div class="rwmb-file-new">
				<input type="file" name="%s[]" class="rwmb-file-input">
				<a class="rwmb-file-add" href="#"><strong>%s</strong></a>
			</div>',
			$field['file_input_name'],
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
		$output        = '';

		foreach ( (array) $files as $k => $file ) {
			// Ignore deleted files (if users accidentally deleted files or uses `force_delete` without saving post).
			if ( get_attached_file( $file ) ) {
				$output .= self::call( $field, 'file_html', $file, $k );
			}
		}
		return sprintf(
			'<ul class="rwmb-uploaded" data-field_id="%s" data-delete_nonce="%s" data-reorder_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s" data-mime_type="%s">%s</ul>',
			$field['id'],
			$delete_nonce,
			$reorder_nonce,
			$field['force_delete'] ? 1 : 0,
			$field['max_file_uploads'],
			$field['mime_type'],
			$output
		);
	}

	/**
	 * Get HTML for uploaded file.
	 *
	 * @param int   $file  Attachment (file) ID.
	 * @param int   $index File index.
	 * @param array $field Field data.
	 * @return string
	 */
	protected static function file_html( $file, $index, $field ) {
		$i18n_delete = apply_filters( 'rwmb_file_delete_string', _x( 'Delete', 'file upload', 'meta-box' ) );
		$i18n_edit   = apply_filters( 'rwmb_file_edit_string', _x( 'Edit', 'file upload', 'meta-box' ) );
		$attributes  = self::get_attributes( $field, $file );
		$path        = get_attached_file( $file );
		$icon        = wp_get_attachment_image( $file, array( 60, 60 ), true );

		return sprintf(
			'<li class="rwmb-file">
				<div class="rwmb-file-icon"><a href="%s" target="_blank">%s</a></div>
				<div class="rwmb-file-info">
					<a href="%s" target="_blank" class="rwmb-file-title">%s</a>
					<p class="rwmb-file-name">%s</p>
					<p class="rwmb-file-actions">
						<a href="%s" class="rwmb-file-edit" target="_blank"><span class="dashicons dashicons-edit"></span>%s</a>
						<a href="#" class="rwmb-file-delete" data-attachment_id="%s"><span class="dashicons dashicons-no-alt"></span>%s</a>
					</p>
				</div>
				<input type="hidden" name="%s[%s]" value="%s">
			</li>',
			wp_get_attachment_url( $file ), $icon,
			wp_get_attachment_url( $file ), get_the_title( $file ),
			basename( $path ),
			get_edit_post_link( $file ), $i18n_edit,
			$file, $i18n_delete,
			$attributes['name'], $index, $file
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
		$input = $field['file_input_name'];

		// @codingStandardsIgnoreLine
		if ( empty( $_FILES[ $input ] ) ) {
			return $new;
		}

		$new = array_filter( (array) $new );

		// Non-cloneable field.
		if ( ! $field['clone'] ) {
			$count = self::transform( $input );
			for ( $i = 0; $i <= $count; $i ++ ) {
				$attachment = media_handle_upload( "{$input}_{$i}", $post_id );
				if ( ! is_wp_error( $attachment ) ) {
					$new[] = $attachment;
				}
			}
			return $new;
		}

		// Cloneable field.
		$counts = self::transform_cloneable( $input );
		foreach ( $counts as $clone_index => $count ) {
			if ( empty( $new[ $clone_index ] ) ) {
				$new[ $clone_index ] = array();
			}
			for ( $i = 0; $i <= $count; $i ++ ) {
				$attachment = media_handle_upload( "{$input}_{$clone_index}_{$i}", $post_id );
				if ( ! is_wp_error( $attachment ) ) {
					$new[ $clone_index ][] = $attachment;
				}
			}
		}

		return $new;
	}

	/**
	 * Transform $_FILES from $_FILES['field']['key']['index'] to $_FILES['field_index']['key'].
	 *
	 * @param string $input_name The field input name.
	 *
	 * @return int The number of uploaded files.
	 */
	protected static function transform( $input_name ) {
		// @codingStandardsIgnoreStart
		foreach ( $_FILES[ $input_name ] as $key => $list ) {
			foreach ( $list as $index => $value ) {
				$file_key = "{$input_name}_{$index}";
				if ( ! isset( $_FILES[ $file_key ] ) ) {
					$_FILES[ $file_key ] = array();
				}
				$_FILES[ $file_key ][ $key ] = $value;
			}
		}

		return count( $_FILES[ $input_name ]['name'] );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Transform $_FILES from $_FILES['field']['key']['cloneIndex']['index'] to $_FILES['field_cloneIndex_index']['key'].
	 *
	 * @param string $input_name The field input name.
	 *
	 * @return array
	 */
	protected static function transform_cloneable( $input_name ) {
		// @codingStandardsIgnoreStart
		foreach ( $_FILES[ $input_name ] as $key => $list ) {
			foreach ( $list as $clone_index => $clone_values ) {
				foreach ( $clone_values as $index => $value ) {
					$file_key = "{$input_name}_{$clone_index}_{$index}";

					if ( ! isset( $_FILES[ $file_key ] ) ) {
						$_FILES[ $file_key ] = array();
					}
					$_FILES[ $file_key ][ $key ] = $value;
				}
			}
		}

		$counts = array();
		foreach ( $_FILES[ $input_name ]['name'] as $clone_index => $clone_values ) {
			$counts[ $clone_index ] = count( $clone_values );
		}
		return $counts;
		// @codingStandardsIgnoreEnd
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

		$field['file_input_name'] = '_file_' . $field['id'];

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
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $value['url'] ), esc_html( $value['title'] ) );
	}
}
