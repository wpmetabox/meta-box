<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

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
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$order    = isset( $_POST['order'] ) ? $_POST['order'] : 0;

			check_admin_referer( "rwmb-reorder-images_{$field_id}" );

			parse_str( $order, $items );
			$items = $items['item'];
			$order = 1;

			foreach ( $items as $item )
			{
				wp_update_post( array(
					'ID'         => $item,
					'menu_order' => $order++,
				) );
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

			$meta = implode( ',' , $meta );

			// Re-arrange images with 'menu_order'
			$meta = $wpdb->get_col( "
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND ID in ({$meta})
				ORDER BY menu_order ASC
			" );

			return (array) $meta;
		}
	}
}