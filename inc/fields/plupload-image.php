<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Plupload_Image_Field' ) )
{
	class RWMB_Plupload_Image_Field extends RWMB_Image_Field
	{
		/**
		 * Add field actions
		 *
		 * @return	void
		 */
		static function add_actions()
		{
			parent::add_actions();
			add_action( 'wp_ajax_rwmb_plupload_image_upload', array( __CLASS__, 'handle_upload' ) );
		}

		/**
		 * Upload
		 * Ajax callback function
		 *
		 * @return string Error or (XML-)response
		 */
		static function handle_upload()
		{
			global $wpdb;
			$post_id = is_numeric( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;
			$field_id = isset( $_REQUEST['field_id'] ) ? $_REQUEST['field_id'] : '';

			check_ajax_referer( "rwmb-upload-images_{$field_id}" );

			// You can use WP's wp_handle_upload() function:
			$file       = $_FILES['async-upload'];
			$file_attr  = wp_handle_upload( $file, array( 'test_form' => false ) );
			//Get next menu_order
			$meta = get_post_meta( $post_id, $field_id, false );
			if( empty( $meta ) ){
				$next = 0;
			} else {
				$meta = implode( ',' , (array) $meta );
				$max = $wpdb->get_var( "
					SELECT MAX(menu_order) FROM {$wpdb->posts}
					WHERE post_type = 'attachment'
					AND ID in ({$meta})
				" );
				$next = is_numeric($max) ? (int) $max + 1: 0;
			}

			$attachment = array(
				'guid'           	=> $file_attr['url'],
				'post_mime_type' 	=> $file_attr['type'],
				'post_title'     	=> preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
				'post_content'   	=> '',
				'post_status'    	=> 'inherit',
				'menu_order'		=> $next
			);

			// Adds file as attachment to WordPress
			$id = wp_insert_attachment( $attachment, $file_attr['file'], $post_id );
			if ( ! is_wp_error( $id ) )
			{
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_attr['file'] ) );

				// Save file ID in meta field
				add_post_meta( $post_id, $field_id, $id, false );

				RW_Meta_Box::ajax_response( self::img_html( $id ), 'success' );
			}

			exit;
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			// Enqueue same scripts and styles as for file field
			parent::admin_enqueue_scripts();
			wp_enqueue_style( 'rwmb-plupload-image', RWMB_CSS_URL . 'plupload-image.css', array( 'wp-admin' ), RWMB_VER );
			wp_enqueue_script( 'rwmb-plupload-image', RWMB_JS_URL . 'plupload-image.js', array( 'jquery-ui-sortable', 'wp-ajax-response', 'plupload-all' ), RWMB_VER, true );
			wp_localize_script( 'rwmb-plupload-image', 'RWMB', array( 'url' => RWMB_URL ) );
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
			if ( ! is_array( $meta ) )
				$meta = ( array ) $meta;

			// Filter to change the drag & drop box background string
			$i18n_drop   = apply_filters( 'rwmb_plupload_image_drop_string', _x( 'Drop images here', 'image upload', 'rwmb' ), $field );
			$i18n_or     = apply_filters( 'rwmb_plupload_image_or_string', _x( 'or', 'image upload', 'rwmb' ), $field );
			$i18n_select = apply_filters( 'rwmb_plupload_image_select_string', _x( 'Select Files', 'image upload', 'rwmb' ), $field );

			// Uploaded images

			// Check for max_file_uploads
			$classes = array( 'rwmb-drag-drop', 'drag-drop', 'hide-if-no-js', 'new-files');
			if ( ! empty( $field['max_file_uploads'] ) && count( $meta ) >= (int) $field['max_file_uploads']  )
				$classes[] = 'hidden';


			$html .= self::get_uploaded_images( $meta, $field );

			// Show form upload
			$html .= sprintf(
				'<div id="%s-dragdrop" class="%s" data-upload_nonce="%s" data-js_options="%s">
					<div class = "drag-drop-inside">
						<p class="drag-drop-info">%s</p>
						<p>%s</p>
						<p class="drag-drop-buttons"><input id="%s-browse-button" type="button" value="%s" class="button" /></p>
					</div>
				</div>',
				$field['id'],
				implode( ' ', $classes ),
				wp_create_nonce( "rwmb-upload-images_{$field['id']}" ),
				esc_attr( json_encode( $field['js_options'] ) ),
				$i18n_drop,
				$i18n_or,
				$field['id'],
				$i18n_select
			);

			return $html;
		}

		/**
		 * Get field value
		 * It's the combination of new (uploaded) images and saved images
		 *
		 * @param array $new
		 * @param array $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			$new = (array) $new;
			return array_unique( array_merge( $old, $new ) );
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
			$field['js_options'] = array(
				'runtimes'				=> 'html5,silverlight,flash,html4',
				'file_data_name'		=> 'async-upload',
				//'container'				=> $field['id'] . '-container',
				'browse_button'			=> $field['id'] . '-browse-button',
				'drop_element'			=> $field['id'] . '-dragdrop',
				'multiple_queues'		=> true,
				'max_file_size'			=> wp_max_upload_size() . 'b',
				'url'					=> admin_url( 'admin-ajax.php' ),
				'flash_swf_url'			=> includes_url( 'js/plupload/plupload.flash.swf' ),
				'silverlight_xap_url'	=> includes_url( 'js/plupload/plupload.silverlight.xap' ),
				'multipart'				=> true,
				'urlstream_upload'		=> true,
				'filters'				=> array(
					array(
						'title'      => _x( 'Allowed Image Files', 'image upload', 'rwmb' ),
						'extensions' => 'jpg,jpeg,gif,png',
					),
				),
				'multipart_params'		=> array(
					'field_id'	=> $field['id'],
					'action' 	=> 'rwmb_plupload_image_upload',
				)
			);
			$field = parent::normalize_field( $field );

			return $field;
		}
	}
}
