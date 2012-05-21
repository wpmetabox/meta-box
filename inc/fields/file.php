<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_File_Field' ) )
{
	class RWMB_File_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_script( 'rwmb-file', RWMB_JS_URL . 'file.js', array( 'jquery', 'wp-ajax-response' ), RWMB_VER, true );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Add data encoding type for file uploading
			add_action( 'post_edit_form_tag', array( __CLASS__, 'post_edit_form_tag' ) );

			// Delete file via Ajax
			add_action( 'wp_ajax_rwmb_delete_file', array( __CLASS__, 'wp_ajax_delete_file' ) );
		}

		/**
		 * Add data encoding type for file uploading
		 *
		 * @return void
		 */
		static function post_edit_form_tag()
		{
			echo ' enctype="multipart/form-data"';
		}

		/**
		 * Ajax callback for deleting files.
		 * Modified from a function used by "Verve Meta Boxes" plugin
		 *
		 * @link http://goo.gl/LzYSq
		 * @return void
		 */
		static function wp_ajax_delete_file()
		{
			$post_id       = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$field_id      = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$attachment_id = isset( $_POST['attachment_id'] ) ? intval( $_POST['attachment_id'] ) : 0;

			check_admin_referer( "rwmb-delete-file_{$field_id}" );

			$ok = delete_post_meta( $post_id, $field_id, $attachment_id );
			$ok = $ok && wp_delete_attachment( $attachment_id );

			if ( $ok )
				RW_Meta_Box::ajax_response( '', 'success' );
			else
				RW_Meta_Box::ajax_response( __( "Error: Cannot delete file", 'rwmb' ), 'error' );
		}

		/**
		 * Get field HTML
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			$i18n_msg      = _x( 'Uploaded files', 'file upload', 'rwmb' );
			$i18n_del_file = _x( 'Delete this file', 'file upload', 'rwmb' );
			$i18n_delete   = _x( 'Delete', 'file upload', 'rwmb' );
			$i18n_title    = _x( 'Upload files', 'file upload', 'rwmb' );
			$i18n_more     = _x( '+ Add new file', 'file upload', 'rwmb' );

			$html  = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			// Uploaded files
			if ( ! empty( $meta ) )
			{
				$html .= "<h4>{$i18n_msg}</h4>";
				$html .= '<ol class="rwmb-uploaded">';

				foreach ( $meta as $attachment_id )
				{
					$attachment = wp_get_attachment_link( $attachment_id );
					$html .= "<li>{$attachment} (<a title='{$i18n_del_file}' class='rwmb-delete-file' href='#' rel='{$attachment_id}'>{$i18n_delete}</a>)</li>";
				}

				$html .= '</ol>';
			}

			// Show form upload
			$html .= "
				<h4>{$i18n_title}</h4>
				<div class='new-files'>
					<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
					<a class='rwmb-add-file' href='#'><strong>{$i18n_more}</strong></a>
				</div>
			";

			return $html;
		}

		/**
		 * Save file field
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$name = $field['id'];
			if ( empty( $_FILES[ $name ] ) )
				return;

			$files	= self::fix_file_array( $_FILES[ $name ] );

			foreach ( $files as $file_item )
			{
				$file = wp_handle_upload( $file_item, array( 'test_form' => false ) );

				if ( ! isset( $file['file'] ) )
					continue;

				$file_name = $file['file'];

				$attachment = array(
					'post_mime_type' => $file['type'],
					'guid'           => $file['url'],
					'post_parent'    => $post_id,
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
					'post_content'   => ''
				);
				$id = wp_insert_attachment( $attachment, $file_name, $post_id );

				if ( ! is_wp_error( $id ) )
				{
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_name ) );

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
		 *
		 * @param array $files
		 *
		 * @return array
		 */
		static function fix_file_array( $files )
		{
			$output = array();
			foreach ( $files as $key => $list )
			{
				foreach ( $list as $index => $value )
				{
					$output[$index][$key] = $value;
				}
			}
			return $output;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field['multiple'] = true;
			$field['std'] = empty( $field['std'] ) ? array() : $field['std'];
			return $field;
		}
	}
}