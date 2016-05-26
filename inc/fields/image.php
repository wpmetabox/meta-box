<?php

/**
 * Image field class which uses <input type="file"> to upload.
 */
class RWMB_Image_Field extends RWMB_File_Field
{
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts()
	{
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-image', RWMB_CSS_URL . 'image.css', array(), RWMB_VER );
	}

	/**
	 * Get HTML markup for ONE uploaded image
	 *
	 * @param int $image Image ID
	 * @return string
	 */
	public static function file_html( $image )
	{
		list( $src ) = wp_get_attachment_image_src( $image, 'thumbnail' );
		return sprintf(
			'<li id="item_%s">
				<img src="%s">
				<div class="rwmb-image-bar">
					<a href="%s" target="_blank"><span class="dashicons dashicons-edit"></span></a> |
					<a class="rwmb-delete-file" href="#" data-attachment_id="%s">&times;</a>
				</div>
			</li>',
			$image,
			$src,
			get_edit_post_link( $image ),
			$image
		);
	}

	/**
	 * Format a single value for the helper functions.
	 * @param array $field Field parameter
	 * @param array $value The value
	 * @return string
	 */
	public static function format_single_value( $field, $value )
	{
		$output = '<ul>';
		foreach ( $value as $file )
		{
			$img = sprintf( '<img src="%s" alt="%s">', esc_url( $file['url'] ), esc_attr( $file['alt'] ) );

			// Link thumbnail to full size image?
			if ( isset( $args['link'] ) && $args['link'] )
			{
				$img = sprintf( '<a href="%s" title="%s">%s</a>', esc_url( $file['full_url'] ), esc_attr( $file['title'] ), $img );
			}
			$output .= "<li>$img</li>";
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Get uploaded file information
	 *
	 * @param int   $file Attachment image ID (post ID). Required.
	 * @param array $args Array of arguments (for size).
	 *
	 * @return array|bool False if file not found. Array of image info on success
	 */
	public static function file_info( $file, $args = array() )
	{
		if ( ! $path = get_attached_file( $file ) )
		{
			return false;
		}

		$args = wp_parse_args( $args, array(
			'size' => 'thumbnail',
		) );
		list( $src ) = wp_get_attachment_image_src( $file, $args['size'] );
		$attachment = get_post( $file );
		$info       = array(
			'ID'          => $file,
			'name'        => basename( $path ),
			'path'        => $path,
			'url'         => $src,
			'full_url'    => wp_get_attachment_url( $file ),
			'title'       => $attachment->post_title,
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
			'alt'         => get_post_meta( $file, '_wp_attachment_image_alt', true ),
		);
		if ( function_exists( 'wp_get_attachment_image_srcset' ) )
		{
			$info['srcset'] = wp_get_attachment_image_srcset( $file );
		}

		return wp_parse_args( $info, wp_get_attachment_metadata( $file ) );
	}
}
