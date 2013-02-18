<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

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
			$force_delete  = isset( $_POST['force_delete'] ) ? intval( $_POST['force_delete'] ) : 0;

			check_admin_referer( "rwmb-delete-file_{$field_id}" );

			delete_post_meta( $post_id, $field_id, $attachment_id );
			$ok = $force_delete ? wp_delete_attachment( $attachment_id ) : true;

			if ( $ok )
				RW_Meta_Box::ajax_response( '', 'success' );
			else
				RW_Meta_Box::ajax_response( __( 'Error: Cannot delete file', 'rwmb' ), 'error' );
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
			$i18n_title  = _x( 'Upload files', 'file upload', 'rwmb' );
			$i18n_more   = _x( '+ Add new file', 'file upload', 'rwmb' );			

			// Uploaded files
			$html = self::get_uploaded_files( $meta, $field );

			// Show form upload
			$html .= sprintf(
				'<h4>%s</h4>
				<div class="new-files">
					<div class="file-input"><input type="file" name="%s[]" /></div>
					<a class="rwmb-add-file" href="#"><strong>%s</strong></a>
				</div>',
				$i18n_title,
				$field['id'],
				$i18n_more
			);

			return $html;
		}
		
		static function get_uploaded_files( $files, $field ) 
		{			
			$delete_nonce = wp_create_nonce( "rwmb-delete-file_{$field['id']}" );
			$ol = '<ol class="rwmb-uploaded" data-field_id="%s" data-delete_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s" data-mime_type="%s">';
			$html .= sprintf(
				$ol,
				$field['id'],
				$delete_nonce,
				$field['force_delete'] ? 1 : 0,
				$field['max_file_uploads'],
				$field['mime_type']
			);
			

			foreach ( $files as $attachment_id )
			{
				$html .= self::file_html( $attachment_id ); 
			}

			$html .= '</ol>';
			
			return $html;
		}
		
		static function file_html( $attachment_id ) 
		{
			$i18n_delete = _x( 'Delete', 'file upload', 'rwmb' );
			$li = '<li>%s (<a title="%s" class="rwmb-delete-file" href="#" data-attachment_id="%s">%s</a>)</li>';
			
			$attachment = wp_get_attachment_link( $attachment_id );
			$mime_type = get_post_mime_type( $attachment_id );
			return sprintf(
				$li,
				$attachment,
				$i18n_delete,
				$attachment_id,
				$i18n_delete
			);
		}

		/**
		 * Get meta values to save
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			$name = $field['id'];
			if ( empty( $_FILES[ $name ] ) )
				return $new;

			$new = array();
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
					'post_content'   => '',
				);
				$id = wp_insert_attachment( $attachment, $file_name, $post_id );

				if ( ! is_wp_error( $id ) )
				{
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_name ) );

					// Save file ID in meta field
					$new[] = $id;
				}
			}

			return array_unique( array_merge( $old, $new ) );
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
			$field = wp_parse_args( $field, array(
				'std'          => array(),
				'force_delete' => false,
				'max_file_uploads' => 0
			) );
			$field['multiple'] = true;
			return $field;
		}
		
		/**
		 * Standard meta retrieval
		 *
		 * @param mixed $meta
		 * @param int   $post_id
		 * @param array $field
		 * @param bool  $saved
		 *
		 * @return mixed
		 */
		static function meta( $meta, $post_id, $saved, $field )
		{
			global $wpdb;

			$meta = RW_Meta_Box::meta( $meta, $post_id, $saved, $field );

			if ( empty( $meta ) )
				return array();

			return (array) $meta;
		}
	}
}