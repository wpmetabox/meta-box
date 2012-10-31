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
				wp_update_post(
					array(
						'ID'         => $item,
						'menu_order' => $order++,
					)
				);
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
			$i18n_title = _x( 'Upload images', 'image upload', 'rwmb' );
			$i18n_more  = _x( '+ Add new image', 'image upload', 'rwmb' );

			$html  = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= wp_nonce_field( "rwmb-reorder-images_{$field['id']}", "nonce-reorder-images_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			// Uploaded images
			if ( ! empty( $meta ) )
				$html .= self::get_uploaded_images( $meta, $field );

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

		/**
		 * Get HTML markup for uploaded images
		 *
		 * @param array $images
		 * @param array $field
		 *
		 * @return string
		 */
		static function get_uploaded_images( $images, $field )
		{
			$html = '<ul class="rwmb-images rwmb-uploaded">';

			foreach ( $images as $image )
			{
				$html .= self::img_html( $image, $field );
			}

			$html .= '</ul>';

			return $html;
		}

		/**
		 * Get HTML markup for ONE uploaded image
		 *
		 * @param int $image Image ID
		 * @param int $field
		 *
		 * @return string
		 */
		static function img_html( $image, $field )
		{
			$i18n_delete = _x( 'Delete', 'image upload', 'rwmb' );
			$i18n_edit   = _x( 'Edit', 'image upload', 'rwmb' );
			$li = '
				<li id="item_%s">
					<img src="%s" />
					<div class="rwmb-image-bar">
						<a title="%s" class="rwmb-edit-file" href="%s" target="_blank">%s</a> |
						<a title="%s" class="rwmb-delete-file" href="#" data-field_id="%s" data-attachment_id="%s" data-force_delete="%s">%s</a>
					</div>
				</li>
			';

			$src  = wp_get_attachment_image_src( $image, 'thumbnail' );
			$src  = $src[0];
			$link = get_edit_post_link( $image );

			return sprintf(
				$li,
				$image,
				$src,
				$i18n_edit, $link, $i18n_edit,
				$i18n_delete, $field['id'], $image, $field['force_delete'] ? 1 : 0, $i18n_delete
			);
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