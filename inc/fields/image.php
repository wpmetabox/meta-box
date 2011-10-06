<?php

if ( !class_exists( 'RW_Meta_Box_Image_Field' ) ) {

	class RW_Meta_Box_Image_Field extends RW_Meta_Box_File_Field {

		/**
		 * Enqueue scripts and styles for image field
		 * Make upload feature works even when custom post type doesn't support 'editor'
		 */
		static function admin_print_styles( ) {
			// Enqueue same scripts and styles as for file field
			parent::admin_print_styles( );

			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'rw-meta-box-image', RW_META_BOX_CSS . 'image.css', RW_META_BOX_VER );

			wp_enqueue_script( 'rw-meta-box-image', RW_META_BOX_JS . 'image.js', array( 'thickbox', 'media-upload', 'jquery-ui-sortable' ), RW_META_BOX_VER, true );
		}

		/**
		 * Add actions for image field
		 */
		static function add_actions( ) {
			// Do same actions as file field
			parent::add_actions( );

			// Process adding multiple images to image meta field
			add_filter( 'media_upload_gallery', array( __CLASS__, 'media_upload' ) );
			add_filter( 'media_upload_library', array( __CLASS__, 'media_upload' ) );
			add_filter( 'media_upload_image', array( __CLASS__, 'media_upload' ) );

			// Reorder images via Ajax
			add_action( 'wp_ajax_rw_meta_box_reorder_images', array( __CLASS__, 'wp_ajax_reorder_images' ) );
		}

		/**
		 * Process adding images to image meta field, modifiy from 'Faster image insert' plugin
		 */
		static function media_upload( ) {
			if ( !isset( $_POST['rw-insert'] ) || empty( $_POST['attachments'] ) )
				return;

			check_admin_referer( 'media-form' );

			$nonce = wp_create_nonce( 'rw_ajax_delete' );
			$post_id = $_POST['post_id'];
			$id = $_POST['field_id'];

			// modify the insertion string
			$html = '';
			foreach ( $_POST['attachments'] as $attachment_id => $attachment ) {
				$attachment = stripslashes_deep( $attachment );
				if ( empty( $attachment['selected'] ) || empty( $attachment['url'] ) )
					continue;

				$li = "<li id='item_$attachment_id'>";
				$li .= "<img src='{$attachment['url']}' />";
				$li .= "<a title='" . __( 'Delete this image' ) . "' class='rw-delete-file' href='#' rel='$nonce|$post_id|$id|$attachment_id'>" . __( 'Delete' ) . "</a>";
				$li .= "<input type='hidden' name='{$id}[]' value='$attachment_id' />";
				$li .= "</li>";
				$html .= $li;
			}

			media_send_to_editor( $html );
		}

		/**
		 * Ajax callback for reordering images
		 */
		static function wp_ajax_reorder_images( ) {
			if ( !isset( $_POST['data'] ) )
				die( );

			list( $order, $post_id, $key, $nonce ) = explode( '|', $_POST['data'] );

			if ( !wp_verify_nonce( $nonce, 'rw_ajax_reorder' ) )
				die( '1' );

			parse_str( $order, $items );
			$items = $items['item'];
			$order = 1;
			foreach ( $items as $item ) {
				wp_update_post( array(
					'ID' => $item,
					'post_parent' => $post_id,
					'menu_order' => $order
				) );
				$order++;
			}

			die( '0' );
		}

		/**
		 * Show HTML markup for image field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			global $wpdb, $post;

			if ( !is_array( $meta ) )
				$meta = (array) $meta;

			$html = '';

			$nonce_delete = wp_create_nonce( 'rw_ajax_delete' );
			$nonce_sort = wp_create_nonce( 'rw_ajax_reorder' );

			$html .= "<input type='hidden' class='rw-images-data' value='{$post->ID}|{$field['id']}|$nonce_sort' />
				  <ul class='rw-images rw-upload' id='rw-images-{$field['id']}'>";

			// Re-arrange images with 'menu_order', thanks Onur
			if ( !empty( $meta ) ) {
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

					$html .= "<li id='item_$image'>
							<img src='$src' />
							<a title='" . __( 'Delete this image' ) . "' class='rw-delete-file' href='#' rel='$nonce_delete|{$post->ID}|{$field['id']}|$image'>" . __( 'Delete' ) . "</a>
							<input type='hidden' name='{$field['id']}[]' value='$image' />
						</li>";
				}
			}
			$html .= '</ul>';

			$html .= "<a href='#' class='rw-upload-button button' rel='{$post->ID}|{$field['id']}'>" . __( 'Add more images' ) . "</a>";

			return $html;
		}
	}
}