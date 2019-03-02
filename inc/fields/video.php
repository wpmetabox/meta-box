<?php
/**
 * Video field which uses WordPress media popup to upload and select video.
 *
 * @package Meta Box
 * @since   4.10
 */

/**
 * The video field class.
 */
class RWMB_Video_Field extends RWMB_Media_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-video', RWMB_CSS_URL . 'video.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-video', RWMB_JS_URL . 'video.js', array( 'rwmb-media' ), RWMB_VER, true );
		RWMB_Helpers_Field::localize_script_once(
			'rwmb-video',
			'i18nRwmbVideo',
			array(
				'extensions' => wp_get_video_extensions(),
			)
		);
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field['mime_type'] = 'video';
		$field              = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file_id Attachment image ID (post ID). Required.
	 * @param array $args    Array of arguments (for size).
	 * @param array $field   Field settings.
	 *
	 * @return array|bool False if file not found. Array of image info on success.
	 */
	public static function file_info( $file_id, $args = array(), $field = array() ) {
		if ( ! get_attached_file( $file_id ) ) {
			return false;
		}
		$attachment = get_post( $file_id );
		$url        = wp_get_attachment_url( $attachment->ID );
		$file_type  = wp_check_filetype( $url, wp_get_mime_types() );
		$data       = array(
			'ID'          => $file_id,
			'src'         => $url,
			'type'        => $file_type['type'],
			'title'       => $attachment->post_title,
			'caption'     => $attachment->post_excerpt,
			'description' => $attachment->post_content,
		);

		$data['meta'] = array();
		$meta         = wp_get_attachment_metadata( $attachment->ID );
		if ( ! empty( $meta ) ) {
			foreach ( wp_get_attachment_id3_keys( $attachment ) as $key => $label ) {
				if ( ! empty( $meta[ $key ] ) ) {
					$data['meta'][ $key ] = $meta[ $key ];
				}
			}

			if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
				$data['dimensions'] = array(
					'width'  => $meta['width'],
					'height' => $meta['height'],
				);
			} else {
				$data['dimensions'] = array(
					'width'  => 640,
					'height' => 360,
				);
			}
		}

		$thumb_id = get_post_thumbnail_id( $attachment->ID );
		if ( ! empty( $thumb_id ) ) {
			list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'full' );
			$data['image']                = compact( 'src', 'width', 'height' );
			list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
			$data['thumb']                = compact( 'src', 'width', 'height' );
		} else {
			$src           = wp_mime_type_icon( $attachment->ID );
			$width         = 48;
			$height        = 64;
			$data['image'] = compact( 'src', 'width', 'height' );
			$data['thumb'] = compact( 'src', 'width', 'height' );
		}

		return $data;
	}

	/**
	 * Format value for a clone.
	 *
	 * @param array        $field   Field parameters.
	 * @param string|array $value   The field meta value.
	 * @param array        $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null     $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_clone_value( $field, $value, $args, $post_id ) {
		$ids = implode( ',', wp_list_pluck( $value, 'ID' ) );

		// Display single video.
		if ( 1 === count( $value ) ) {
			$video = reset( $value );
			return wp_video_shortcode(
				array(
					'src'    => $video['src'],
					'width'  => $video['dimensions']['width'],
					'height' => $video['dimensions']['height'],
				)
			);
		}

		// Display multiple videos in a playlist.
		return wp_playlist_shortcode(
			array(
				'ids'  => $ids,
				'type' => 'video',
			)
		);
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		require_once RWMB_INC_DIR . 'templates/video.php';
	}
}
