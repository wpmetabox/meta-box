<?php

if ( !class_exists( 'RWMB_File_Field' ) ) {

	class RWMB_File_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_enqueue_script( 'rwmb-file', RWMB_JS . 'file.js', array( 'jquery' ), RWMB_VER, true );
		}

		/**
		 * Add actions
		 */
		static function add_actions( ) {
			// Add data encoding type for file uploading
			add_action( 'post_edit_form_tag', array( __CLASS__, 'post_edit_form_tag' ) );

			// Delete file via Ajax
			add_action( 'wp_ajax_rwmb_delete_file', array( __CLASS__, 'wp_ajax_delete_file' ) );
		}

		/**
		 * Add data encoding type for file uploading
		 */
		static function post_edit_form_tag( ) {
			echo ' enctype="multipart/form-data"';
		}

		/**
		 * Ajax callback for deleting files.
		 * Modified from a function used by "Verve Meta Boxes" plugin (http://goo.gl/LzYSq)
		 */
		static function wp_ajax_delete_file( ) {
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;

			check_admin_referer( "rwmb-delete-file_{$field_id}" );

			$ok = delete_post_meta( $post_id, $field_id, $attachment_id );
			$ok = $ok && wp_delete_attachment( $attachment_id );

			if ( $ok )
				die( RW_Meta_Box::format_response( '', 'success' ) );
			else
				die( RW_Meta_Box::format_response( __( 'Cannot delete file. Something\'s wrong.', RWMB_TEXTDOMAIN ), 'error' ) );
		}

		/**
		 * Show HTML markup for file field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {

			if ( !is_array( $meta ) )
				$meta = (array) $meta;

			$html = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			if ( !empty( $meta ) ) {
				$html .= '<h4>' . __( 'Uploaded files', RWMB_TEXTDOMAIN ) . '</h4>';
				$html .= '<ol class="rwmb-uploaded">';

				foreach ( $meta as $attachment_id ) {
					$html .= "<li>" . wp_get_attachment_link( $attachment_id ) . " (<a class='rwmb-delete-file' href='#' rel='$attachment_id'>" . __( 'Delete', RWMB_TEXTDOMAIN ) . "</a>)</li>";
				}

				$html .= '</ol>';
			}

			// Show form upload
			$html .= "<h4>" . __( 'Upload new files', RWMB_TEXTDOMAIN ) . "</h4>
			<div class='new-files'>
				<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
				<a class='rwmb-add-file' href='#'>" . __( 'Add more file', RWMB_TEXTDOMAIN ) . "</a>
			</div>";

			return $html;
		}

		/**
		 * Save file field
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 */
		static function save( $post_id, $field, $old, $new ) {
			$name = $field['id'];
			if ( empty( $_FILES[$name] ) )
				return;

			$files = self::fix_file_array( $_FILES[$name] );

			foreach ( $files as $fileitem ) {
				$file = wp_handle_upload( $fileitem, array( 'test_form' => false ) );

				if ( !isset( $file['file'] ) )
					continue;
				$filename = $file['file'];

				$attachment = array(
					'post_mime_type' => $file['type'],
					'guid' => $file['url'],
					'post_parent' => $post_id,
					'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content' => ''
				);
				$id = wp_insert_attachment( $attachment, $filename, $post_id );
				if ( !is_wp_error( $id ) ) {
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $filename ) );

					// Save file ID in meta field
					add_post_meta( $post_id, $name, $id, false );
				}
			}
		}

		/**
		 * Fixes the odd indexing of multiple file uploads from the format:
		 *	 $_FILES['field']['key']['index']
		 * To the more standard and appropriate:
		 *	 $_FILES['field']['index']['key']
		 * @param $files
		 * @return array
		 */
		static function fix_file_array( $files ) {
			$output = array( );
			foreach ( $files as $key => $list ) {
				foreach ( $list as $index => $value ) {
					$output[$index][$key] = $value;
				}
			}
			return $output;
		}
	}
}