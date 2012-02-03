<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Plupload_Image_Field' ) )
{
	class RWMB_Plupload_Image_Field extends RWMB_Image_Field
	{
		/**
		 * Add field actions
		 *
		 * @return	void
		 */
		static function add_actions( )
		{
			parent::add_actions();
			add_action( 'wp_ajax_plupload_image_upload', array( __CLASS__ , 'handle_upload' ) );
		}

		/**
		 * Upload
		 * Ajax callback function
		 *
		 * @return error or (XML-)response
		 */
		static function handle_upload ()
		{
			header( 'Content-Type: text/html; charset=UTF-8' );

			if ( ! defined('DOING_AJAX' ) )
				define( 'DOING_AJAX', true );

			check_ajax_referer('plupload_image');

			$post_id = 0;
			if ( is_numeric( $_REQUEST['post_id'] ) )
				$post_id = (int) $_REQUEST['post_id'];

			// you can use WP's wp_handle_upload() function:
			$file = $_FILES['async-upload'];
			$file_attr = wp_handle_upload( $file, array('test_form'=>true, 'action' => 'plupload_image_upload') );
			$attachment = array(
				'post_mime_type'	=> $file_attr['type'],
				'post_title'		=> preg_replace( '/\.[^.]+$/', '', basename( $file['name'] ) ),
				'post_content'		=> '',
				'post_status'		=> 'inherit'
			);

			// Adds file as attachment to WordPress
			$id = wp_insert_attachment( $attachment, $file_attr['file'], $post_id );
			if ( ! is_wp_error( $id ) )
			{
				$response = new WP_Ajax_Response();
				wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_attr['file'] ) );
				if ( isset( $_REQUEST['field_id'] ) )
				{
					// Save file ID in meta field
					add_post_meta( $post_id, $_REQUEST['field_id'], $id, false );
				}
				$src = wp_get_attachment_image_src( $id, 'thumbnail' );
				$response->add( array(
					'what'			=>'rwmb_image_response',
					'data'			=> $id,
					'supplemental'	=> array(
						'thumbnail'	=>  $src[0],
						'edit_link'	=> get_edit_post_link($id)
					)
				) );
				$response->send();
			}
			// faster than die();
			exit;
		}

		/**
		 * Add default value for 'image' field
		 *
		 * @param $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field['multiple'] = true;
			return $field;
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_print_styles()
		{
			global $post;
			// Enqueue same scripts and styles as for file field
			parent::admin_print_styles();
			wp_enqueue_script( 'plupload-all' );

			wp_enqueue_style( 'rwmb-plupload-image', RWMB_CSS_URL.'plupload-image.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-plupload-image', RWMB_JS_URL.'plupload-image.js', array( 'jquery-ui-sortable', 'wp-ajax-response', 'plupload-all' ), RWMB_VER, true );
			wp_localize_script( 'rwmb-plupload-image', 'rwmb_plupload_defaults', array(
				'runtimes'				=> 'html5,silverlight,flash,html4',
				'file_data_name'		=> 'async-upload',
				'multiple_queues'		=> true,
				'max_file_size'			=> wp_max_upload_size().'b',
				'url'					=> admin_url('admin-ajax.php'),
				'flash_swf_url'			=> includes_url( 'js/plupload/plupload.flash.swf' ),
				'silverlight_xap_url'	=> includes_url( 'js/plupload/plupload.silverlight.xap' ),
				'filters'				=> array( array( 'title' => _x( 'Allowed Image Files', 'image upload', RWMB_TEXTDOMAIN ), 'extensions' => 'jpg,gif,png' ) ),
				'multipart'				=> true,
				'urlstream_upload'		=> true,
				// additional post data to send to our ajax hook
				'multipart_params'		=> array(
					'_ajax_nonce'	=> wp_create_nonce( 'plupload_image' ),
					'action'    	=> 'plupload_image_upload',  // the ajax action name
					'post_id'		=> $post->ID
				)

			));

			//Links to loading and error images to allow preloading
			wp_localize_script('rwmb-plupload-image','rwmb_plupload_status_icons', array(
				'error' =>  RWMB_URL . "img/image-error.gif",
				'loading' =>  RWMB_URL . "img/image-loading.gif"
			));
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
			global $wpdb;

			if ( ! is_array( $meta ) )
				$meta = (array) $meta;

			$i18n_msg		= _x( 'Uploaded files', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_del_file	= _x( 'Delete this file', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_delete	= _x( 'Delete', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_edit		= _x( 'Edit', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_title		= _x( 'Upload files', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_more		= _x( 'Add another file', 'image upload', RWMB_TEXTDOMAIN );

			// Filter to change the drag & drop box background string
			$i18n_drop		= apply_filters( 'rwmb_upload_drop_string', _x( 'Drop images here', 'image upload', RWMB_TEXTDOMAIN ) );
			$i18n_or        = _x( 'or', 'image upload', RWMB_TEXTDOMAIN );
			$i18n_select	= _x( 'Select Files', 'image upload', RWMB_TEXTDOMAIN );
			$img_prefix		= $field['id'];

			$html  = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= wp_nonce_field( "rwmb-reorder-images_{$field['id']}", "nonce-reorder-images_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id rwmb-image-prefix' value='{$field['id']}' />";

			//Uploaded images
			$html .= "<div id='{$img_prefix}-container'>";
			$html .= "<h4 class='rwmb-uploaded-title'>{$i18n_msg}</h4>";
			$html .= "<ul class='rwmb-images rwmb-uploaded'>";

			foreach ( $meta as $image )
			{
				$src = wp_get_attachment_image_src( $image, 'thumbnail' );
				$src = $src[0];
				$link = get_edit_post_link( $image );

				$html .= "
				<li id='item_{$image}'>
					<img src='{$src}' />
					<div class='rwmb-image-bar'>
						<a title='{$i18n_edit}' class='rwmb-edit-file' href='{$link}' target='_blank'>{$i18n_edit}</a> |
						<a title='{$i18n_del_file}' class='rwmb-delete-file' href='#' rel='{$image}'>{$i18n_delete}</a>
					</div>
				</li>";
			}

			//Template image node
			$html .= "
			<li id='item_' class='hidden rwmb-image-template'>
				<img id='' class='rwmb-image' src='' />
				<div class='rwmb-image-bar hidden'>
					<a title='{$i18n_edit}' class='rwmb-edit-file' href = ''>{$i18n_edit}</a> |
					<a title='{$i18n_del_file}' class='rwmb-delete-file' href='#' rel=''>{$i18n_delete}</a>
				</div>
			</li>";
			$html .= '</ul>';

			// Show form upload
			$html .= "
			<h4>{$i18n_title}</h4>
			<div id='{$img_prefix}-dragdrop' class='rwmb-drag-drop hide-if-no-js'>
				<div class = 'rwmb-drag-drop-inside'>
					<p>{$i18n_drop}</p>
					<p>{$i18n_or}</p>
					<p><input id='{$img_prefix}-browse-button' type='button' value='{$i18n_select}' class='button' /></p>
				</div>
			</div>";

			// old style if no js
			$html .= "
			<div class='new-files hide-if-js'>
				<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
				<a class='rwmb-add-file' href='#'>{$i18n_more}</a>
			</div>";

			$html .= "</div>";

			return $html;
		}
	}
}