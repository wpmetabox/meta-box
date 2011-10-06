<?php

if ( !class_exists( 'RW_Meta_Box_File_Field' ) ) {

	class RW_Meta_Box_File_Field {

		/**
		 * Enqueue scripts and styles for file field
		 */
		static function admin_print_styles( ) {
			wp_enqueue_script( 'rw-meta-box-file', RW_META_BOX_JS . 'file.js', array( 'jquery' ), RW_META_BOX_VER, true );
		}

		/**
		 * Add actions for file field
		 */
		static function add_actions( ) {
			// Add data encoding type for file uploading
			add_action( 'post_edit_form_tag', array( __CLASS__, 'post_edit_form_tag' ) );

			// Delete file via Ajax
			add_action( 'wp_ajax_rw_meta_box_delete_file', array( __CLASS__, 'wp_ajax_delete_file' ) );
		}

		/**
		 * Add data encoding type for file uploading
		 * Make function static can prevents echoing multiple times
		 */
		static function post_edit_form_tag( ) {
			echo ' enctype="multipart/form-data"';
		}

		/**
		 * Ajax callback for deleting files.
		 * Modified from a function used by "Verve Meta Boxes" plugin (http://goo.gl/LzYSq)
		 */
		static function wp_ajax_delete_file( ) {
			if ( !isset( $_POST['data'] ) )
				die( );

			list( $nonce, $post_id, $key, $attach_id ) = explode( '|', $_POST['data'] );

			if ( !wp_verify_nonce( $nonce, 'rw_ajax_delete' ) )
				die( '1' );

			// wp_delete_attachment($attach_id);
			delete_post_meta( $post_id, $key, $attach_id );

			die( '0' );
		}

		/**
		 * Show HTML markup for file field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			global $post;

			if ( !is_array( $meta ) )
				$meta = (array) $meta;

			$html = '';

			if ( !empty( $meta ) ) {
				$nonce = wp_create_nonce( 'rw_ajax_delete' );
				$html .= '<div style="margin-bottom: 10px"><strong>' . __( 'Uploaded files' ) . '</strong></div>';
				$html .= '<ol class="rw-upload">';
				foreach ( $meta as $att ) {
					$html .= "<li>" . wp_get_attachment_link( $att, '', false, false, ' ' ) . " (<a class='rw-delete-file' href='#' rel='$nonce|{$post->ID}|{$field['id']}|$att'>" . __( 'Delete' ) . "</a>)</li>";
				}
				$html .= '</ol>';
			}

			// Show form upload
			$html .= "<div style='clear: both'><strong>" . __( 'Upload new files' ) . "</strong></div>
			<div class='new-files'>
				<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
				<a class='rw-add-file' href='#'>" . __( 'Add more file' ) . "</a>
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

				if ( empty( $file['file'] ) )
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
					add_post_meta( $post_id, $name, $id, false ); // Save file ID in meta field
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