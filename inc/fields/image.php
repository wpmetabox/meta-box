<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Image_Field' ) )
{
	class RWMB_Image_Field extends RWMB_File_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			// Enqueue same scripts and styles as for file field
			parent::admin_enqueue_scripts();

			wp_enqueue_style( 'rwmb-image', RWMB_CSS_URL . 'image.css', array(), RWMB_VER );

			wp_enqueue_script( 'rwmb-image', RWMB_JS_URL . 'image.js', array( 'jquery-ui-sortable', 'wp-ajax-response' ), RWMB_VER, true );
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

			// Reorder images via Ajax
			add_action( 'wp_ajax_rwmb_reorder_images', array( __CLASS__, 'wp_ajax_reorder_images' ) );
		}

		/**
		 * Ajax callback for reordering images
		 *
		 * @return void
		 */
		static function wp_ajax_reorder_images()
		{
			$post_id  = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$order    = isset( $_POST['order'] ) ? $_POST['order'] : 0;

			check_admin_referer( "rwmb-reorder-images_{$field_id}" );

			parse_str( $order, $items );
			$items = $items['item'];
			$order = 1;

			// Delete old meta values
			delete_post_meta( $post_id, $field_id );
			foreach ( $items as $item )
			{
				wp_update_post( array(
					'ID'          => $item,
					'post_parent' => $post_id,
					'menu_order'  => $order ++
				) );

				// Save images in that order to meta field
				// That helps retrieving values easier
				add_post_meta( $post_id, $field_id, $item, false );
			}

			RW_Meta_Box::ajax_response( __( 'Order saved', 'rwmb' ), 'success' );
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

			$i18n_msg      = _x( 'Uploaded files', 'image upload', 'rwmb' );
			$i18n_del_file = _x( 'Delete this file', 'image upload', 'rwmb' );
			$i18n_delete   = _x( 'Delete', 'image upload', 'rwmb' );
			$i18n_edit     = _x( 'Edit', 'image upload', 'rwmb' );
			$i18n_title    = _x( 'Upload files', 'image upload', 'rwmb' );
			$i18n_more     = _x( '+ Add new image', 'image upload', 'rwmb' );

			$html  = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= wp_nonce_field( "rwmb-reorder-images_{$field['id']}", "nonce-reorder-images_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			// Uploaded images
			if ( ! empty( $meta ) )
			{
				$html .= "<h4>{$i18n_msg}</h4>";
				$html .= "<ul class='rwmb-images rwmb-uploaded'>";

				// Change $meta order using the posts 'menu_order'
				// $meta_menu_order = array();
				// foreach ( $meta as $post_id )
				// {
					// $post_meta = get_post( $post_id );
					// $meta_menu_order[$post_meta->menu_order] = $post_id;
				// }
				// ksort( $meta_menu_order );
				// $meta = $meta_menu_order;

				foreach ( $meta as $image )
				{
					$src = wp_get_attachment_image_src( $image, 'thumbnail' );
					$src = $src[0];
					$link = get_edit_post_link( $image );

					$html .= "<li id='item_{$image}'>
						<img src='{$src}' />
						<div class='rwmb-image-bar'>
							<a title='{$i18n_edit}' class='rwmb-edit-file' href='{$link}' target='_blank'>{$i18n_edit}</a> |
							<a title='{$i18n_del_file}' class='rwmb-delete-file' href='#' rel='{$image}'>{$i18n_delete}</a>
						</div>
					</li>";
				}

				$html .= '</ul>';
			}

			// Show form upload
			$html .= "
			<h4>{$i18n_title}</h4>
			<div class='new-files'>
				<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
				<a class='rwmb-add-file' href='#'><strong>{$i18n_more}</strong></a>
			</div>";

			return $html;
		}
	}
}