<?php

if ( !class_exists( 'RWMB_Image_Field' ) ) {

	class RWMB_Image_Field extends RWMB_File_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			// Enqueue same scripts and styles as for file field
			parent::admin_print_styles( );

			wp_enqueue_style( 'rwmb-image', RWMB_CSS_URL . 'image.css', array( ), RWMB_VER );

			wp_enqueue_script( 'rwmb-image', RWMB_JS_URL . 'image.js', array( 'jquery-ui-sortable', 'wp-ajax-response' ), RWMB_VER, true );
		}

		/**
		 * Add actions
		 */
		static function add_actions( ) {
			// Do same actions as file field
			parent::add_actions( );

			// Reorder images via Ajax
			add_action( 'wp_ajax_rwmb_reorder_images', array( __CLASS__, 'wp_ajax_reorder_images' ) );
		}

		/**
		 * Ajax callback for reordering images
		 */
		static function wp_ajax_reorder_images( ) {
			$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
			$field_id = isset( $_POST['field_id'] ) ? $_POST['field_id'] : 0;
			$order = isset( $_POST['order'] ) ? $_POST['order'] : 0;

			check_admin_referer( "rwmb-reorder-images_{$field_id}" );

			parse_str( $order, $items );
			$items = $items['item'];
			$order = 1;
			foreach ( $items as $item ) {
				wp_update_post( array(
					'ID' => $item,
					'post_parent' => $post_id,
					'menu_order' => $order++
				) );
			}

			RW_Meta_Box::ajax_response( __( 'Order saved.', RWMB_TEXTDOMAIN ), 'success' );
		}

		/**
		 * Get field HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			global $wpdb;

			if ( !is_array( $meta ) )
				$meta = (array) $meta;

			$html = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= wp_nonce_field( "rwmb-reorder-images_{$field['id']}", "nonce-reorder-images_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			// Re-arrange images with 'menu_order', thanks Onur
			if ( !empty( $meta ) ) {
				$html .= '<h4>' . __( 'Uploaded images', RWMB_TEXTDOMAIN ) . '</h4>';
				$html .= "<ul class='rwmb-images rwmb-uploaded'>";

				$meta = implode( ',', $meta );
				$images = $wpdb->get_col( "
					SELECT ID FROM $wpdb->posts
					WHERE post_type = 'attachment'
					AND ID in ($meta)
					ORDER BY menu_order ASC
				" );
				foreach ( $images as $image ) {
					$src = wp_get_attachment_image_src( $image );
					$src = $src[0];

					$html .= "<li id='item_{$image}'>
						<img src='{$src}' />
						<a title='" . __( 'Delete this image', RWMB_TEXTDOMAIN ) . "' class='rwmb-delete-file' href='#' rel='$image'>" . __( 'Delete', RWMB_TEXTDOMAIN ) . "</a>
					</li>";
				}

				$html .= '</ul>';
			}

			// Show form upload
			$html .= "<h4>" . __( 'Upload new images', RWMB_TEXTDOMAIN ) . "</h4>
			<div class='new-files'>
				<div class='file-input'><input type='file' name='{$field['id']}[]' /></div>
				<a class='rwmb-add-file' href='#'>" . __( 'Add more file', RWMB_TEXTDOMAIN ) . "</a>
			</div>";

			return $html;
		}
	}
}