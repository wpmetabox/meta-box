<?php
// Prevent loading this file directly - Busted!
if( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Thickbox_Image_Field' ) )
{
	class RWMB_Thickbox_Image_Field extends RWMB_Image_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			parent::admin_enqueue_scripts();

			add_thickbox();
			wp_enqueue_script('media-upload');

			wp_enqueue_script( 'rwmb-thickbox-image', RWMB_JS_URL . 'thickbox-image.js', array( 'jquery' ), RWMB_VER, true );
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

			$i18n_msg    = _x( 'Uploaded files', 'image upload', 'rwmb' );
			$i18n_delete = _x( 'Delete', 'image upload', 'rwmb' );
			$i18n_edit   = _x( 'Edit', 'image upload', 'rwmb' );
			$i18n_upload = _x( 'Upload image', 'image upload', 'rwmb' );

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
							<a title='{$i18n_delete}' class='rwmb-delete-file' href='#' rel='{$image}'>{$i18n_delete}</a>
						</div>
					</li>";
				}

				$html .= '</ul>';
			}
			else
			{
				// Place holder for images
				$html .= "<ul class='rwmb-images rwmb-uploaded'></ul>";
			}

			// Show form upload
			$html .= "<a href='#' class='button-secondary rwmb-thickbox-upload' rel='{$field['id']}'>{$i18n_upload}</a>";

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

			delete_post_meta( $post_id, $name );
			if ( !is_array( $new ) || empty( $new ) )
				return;

			foreach ( $new as $add_new )
			{
				add_post_meta( $post_id, $name, $add_new, false );
			}
		}
	}
}