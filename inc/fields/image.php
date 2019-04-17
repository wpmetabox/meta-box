<?php
/**
 * The image field which uploads images via HTML <input type="file">.
 *
 * @package Meta Box
 */

/**
 * Image field class which uses <input type="file"> to upload.
 */
class RWMB_Image_Field extends RWMB_File_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_media();
		wp_enqueue_style( 'rwmb-image', RWMB_CSS_URL . 'image.css', array(), RWMB_VER );
	}

	/**
	 * Get HTML for uploaded file.
	 *
	 * @param int   $file  Attachment (file) ID.
	 * @param int   $index File index.
	 * @param array $field Field data.
	 *
	 * @return string
	 */
	protected static function file_html( $file, $index, $field ) {
		$attributes = self::get_attributes( $field, $file );

		$edit_link = get_edit_post_link( $file );
		if ( $edit_link ) {
			$edit_link = sprintf( '<a href="%s" class="rwmb-image-edit" target="_blank"><span class="dashicons dashicons-edit"></span></a>', $edit_link );
		}

		return sprintf(
			'<li class="rwmb-image-item attachment %s">
				<input type="hidden" name="%s[%s]" value="%s">
				<div class="attachment-preview">
					<div class="thumbnail">
						<div class="centered">
							%s
						</div>
					</div>
				</div>
				<div class="rwmb-image-overlay"></div>
				<div class="rwmb-image-actions">
					%s
					<a href="#" class="rwmb-image-delete rwmb-file-delete" data-attachment_id="%s"><span class="dashicons dashicons-no-alt"></span></a>
				</div>
			</li>',
			esc_attr( $field['image_size'] ),
			$attributes['name'],
			$index,
			$file,
			wp_get_attachment_image( $file, $field['image_size'] ),
			$edit_link,
			$file
		);
	}

	/**
	 * Normalize field settings.
	 *
	 * @param array $field Field settings.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args(
			$field,
			array(
				'image_size' => 'thumbnail',
			)
		);

		return $field;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		$output = sprintf( '<img src="%s" alt="%s">', esc_url( $value['url'] ), esc_attr( $value['alt'] ) );

		// Link thumbnail to full size image?
		if ( ! empty( $args['link'] ) ) {
			$output = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $value['full_url'] ), esc_attr( $value['title'] ), $output );
		}
		return $output;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file  Attachment image ID (post ID). Required.
	 * @param array $args  Array of arguments (for size).
	 * @param array $field Field settings.
	 *
	 * @return array|bool False if file not found. Array of image info on success.
	 */
	public static function file_info( $file, $args = array(), $field = array() ) {
		$path = get_attached_file( $file );
		if ( ! $path ) {
			return false;
		}

		$args       = wp_parse_args(
			$args,
			array(
				'size' => 'thumbnail',
			)
		);
		$image      = wp_get_attachment_image_src( $file, $args['size'] );
		$attachment = get_post( $file );
		$info       = array(
			'ID'          => $file,
			'name'        => basename( $path ),
			'path'        => $path,
			'url'         => $image[0],
			'full_url'    => wp_get_attachment_url( $file ),
			'title'       => $attachment->post_title,
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'alt'         => get_post_meta( $file, '_wp_attachment_image_alt', true ),
		);
		if ( function_exists( 'wp_get_attachment_image_srcset' ) ) {
			$info['srcset'] = wp_get_attachment_image_srcset( $file, $args['size'] );
		}

		$info = wp_parse_args( $info, wp_get_attachment_metadata( $file ) );

		// Do not overwrite width and height by returned value of image meta.
		$info['width']  = $image[1];
		$info['height'] = $image[2];

		return $info;
	}
}
