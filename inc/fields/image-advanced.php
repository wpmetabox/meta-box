<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
require_once RWMB_FIELDS_DIR . 'image.php';
if ( ! class_exists( 'RWMB_Image_Advanced_Field' ) )
{
	class RWMB_Image_Advanced_Field extends RWMB_Image_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			parent::admin_enqueue_scripts();
			wp_enqueue_script( 'rwmb-image-advanced', RWMB_JS_URL . 'image-advanced.js', array( 'jquery' ), RWMB_VER, true );
		}
		
		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Do same actions as file field
			parent::add_actions();

			// Attach images via Ajax
			add_action( 'wp_ajax_rwmb_attach_media', array( __CLASS__, 'wp_ajax_attach_media' ) );
		}
		
		/**
		 * Ajax callback for attaching media to field
		 *
		 * @return void
		 */
		static function wp_ajax_attach_media()
		{
			$post_id = is_numeric( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$attachment_id    = isset( $_POST['attachment_id'] ) ? $_POST['attachment_id'] : 0;

			check_admin_referer( "rwmb-attach-media_{$field_id}" );
			
			add_post_meta( $post_id, $field_id, $attachment_id, false );
			
			RW_Meta_Box::ajax_response( self::img_html( $attachment_id ), 'success' );
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
			$i18n_title = _x( 'Select or Upload Images', 'image upload', 'rwmb' );
			$attach_nonce = wp_create_nonce( "rwmb-attach-media_{$field['id']}" );

			// Uploaded images
			$html .= self::get_uploaded_images( $meta, $field );

			// Show form upload
			$classes = array( 'button', 'rwmb-image-advanced-upload', 'hide-if-no-js' );
			if ( ! empty( $field['max_file_uploads'] ) )
			{
				$max_file_uploads = (int) $field['max_file_uploads'];
				if ( count( $meta ) >= $max_file_uploads )
					$classes[] = 'hidden';
			}
			$classes = implode( ' ', $classes );
			$html .= "<a href='#' class='{$classes}' data-attach_media_nonce={$attach_nonce}>{$i18n_title}</a>";

			return $html;
		}

	}
}