<?php

/**
 * Image field class which uses <input type="file"> to upload.
 */
class RWMB_Image_Field extends RWMB_File_Field
{
	/**
	 * Enqueue scripts and styles.
	 */
	static function admin_enqueue_scripts()
	{
		// Enqueue same scripts and styles as for file field
		parent::admin_enqueue_scripts();

		wp_enqueue_style( 'rwmb-image', RWMB_CSS_URL . 'image.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-image', RWMB_JS_URL . 'image.js', array( 'jquery-ui-sortable' ), RWMB_VER, true );
	}

	/**
	 * Add custom actions.
	 */
	static function add_actions()
	{
		// Do same actions as file field
		parent::add_actions();

		// Reorder images via Ajax
		add_action( 'wp_ajax_rwmb_reorder_images', array( __CLASS__, 'wp_ajax_reorder_images' ) );
	}

	/**
	 * Ajax callback for reordering images.
	 */
	static function wp_ajax_reorder_images()
	{
		$post_id  = (int) filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
		$field_id = (string) filter_input( INPUT_POST, 'field_id' );
		$order    = (string) filter_input( INPUT_POST, 'order' );

		check_ajax_referer( "rwmb-reorder-images_{$field_id}" );
		parse_str( $order, $items );
		delete_post_meta( $post_id, $field_id );
		foreach ( $items['item'] as $item )
		{
			add_post_meta( $post_id, $field_id, $item, false );
		}
		wp_send_json_success();
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$i18n_title = apply_filters( 'rwmb_image_upload_string', _x( 'Upload Images', 'image upload', 'meta-box' ), $field );
		$i18n_more  = apply_filters( 'rwmb_image_add_string', _x( '+ Add new image', 'image upload', 'meta-box' ), $field );

		// Uploaded images
		$html = self::get_uploaded_images( $meta, $field );

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
		$reorder_nonce = wp_create_nonce( "rwmb-reorder-images_{$field['id']}" );
		$delete_nonce  = wp_create_nonce( "rwmb-delete-file_{$field['id']}" );
		$classes       = array( 'rwmb-images', 'rwmb-uploaded' );
		if ( count( $images ) <= 0 )
			$classes[] = 'hidden';
		$list = '<ul class="%s" data-field_id="%s" data-delete_nonce="%s" data-reorder_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s">';
		$html = sprintf(
			$list,
			implode( ' ', $classes ),
			$field['id'],
			$delete_nonce,
			$reorder_nonce,
			$field['force_delete'] ? 1 : 0,
			$field['max_file_uploads']
		);

		foreach ( $images as $image )
		{
			$html .= self::img_html( $image );
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * Get HTML markup for ONE uploaded image
	 *
	 * @param int $image Image ID
	 * @return string
	 */
	static function img_html( $image )
	{
		$i18n_delete = apply_filters( 'rwmb_image_delete_string', _x( 'Delete', 'image upload', 'meta-box' ) );
		$i18n_edit   = apply_filters( 'rwmb_image_edit_string', _x( 'Edit', 'image upload', 'meta-box' ) );
		$item        = '
			<li id="item_%s">
				<img src="%s" />
				<div class="rwmb-image-bar">
					<a title="%s" class="rwmb-edit-file" href="%s" target="_blank">%s</a> |
					<a title="%s" class="rwmb-delete-file" href="#" data-attachment_id="%s">&times;</a>
				</div>
			</li>
		';

		$src  = wp_get_attachment_image_src( $image, 'thumbnail' );
		$src  = $src[0];
		$link = get_edit_post_link( $image );

		return sprintf(
			$item,
			$image,
			$src,
			$i18n_edit, $link, $i18n_edit,
			$i18n_delete, $image
		);
	}

	/**
	 * Output the field value
	 * Display unordered list of images with option for size and link to full size
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = self::get_value( $field, $args, $post_id );
		if ( ! $value )
			return '';

		$output = '<ul>';
		foreach ( $value as $file_info )
		{
			$img = sprintf(
				'<img src="%s" alt="%s" title="%s">',
				esc_url( $file_info['url'] ),
				esc_attr( $file_info['alt'] ),
				esc_attr( $file_info['title'] )
			);

			// Link thumbnail to full size image?
			if ( isset( $args['link'] ) && $args['link'] )
			{
				$img = sprintf(
					'<a href="%s" title="%s">%s</a>',
					esc_url( $file_info['full_url'] ),
					esc_attr( $file_info['title'] ),
					$img
				);
			}

			$output .= "<li>$img</li>";
		}
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Get uploaded file information
	 *
	 * @param int   $file_id Attachment image ID (post ID). Required.
	 * @param array $args    Array of arguments (for size).
	 *
	 * @return array|bool False if file not found. Array of image info on success
	 */
	static function file_info( $file_id, $args = array() )
	{
		$args = wp_parse_args( $args, array(
			'size' => 'thumbnail',
		) );

		$img_src = wp_get_attachment_image_src( $file_id, $args['size'] );
		if ( ! $img_src )
		{
			return false;
		}

		$attachment = get_post( $file_id );
		$path       = get_attached_file( $file_id );
		$info       = array(
			'ID'          => $file_id,
			'name'        => basename( $path ),
			'path'        => $path,
			'url'         => $img_src[0],
			'width'       => $img_src[1],
			'height'      => $img_src[2],
			'full_url'    => wp_get_attachment_url( $file_id ),
			'title'       => $attachment->post_title,
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'alt'         => get_post_meta( $file_id, '_wp_attachment_image_alt', true ),
		);
		if ( function_exists( 'wp_get_attachment_image_srcset' ) )
		{
			$info['srcset'] = wp_get_attachment_image_srcset( $file_id );
		}
		return $info;
	}
}
