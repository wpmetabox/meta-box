<?php
/**
 * Video field which uses WordPress media popup to upload and select video.
 *
 * @package Meta Box
 */

/**
 * The video field class.
 */
class RWMB_Video_Field extends RWMB_Media_Field {
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-video', RWMB_CSS_URL . 'video.css', array( 'rwmb-media' ), RWMB_VER );
		wp_enqueue_script( 'rwmb-video', RWMB_JS_URL . 'video.js', array( 'rwmb-media' ), RWMB_VER, true );
		self::localize_script( 'rwmb-video', 'i18nRwmbVideo', array(
			'extensions' => wp_get_video_extensions(),
		) );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field              = parent::normalize( $field );
		$field['mime_type'] = 'video';

		return $field;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int $file_id Attachment image ID (post ID). Required.
	 * @param array $args Array of arguments (for size).
	 *
	 * @return array|bool False if file not found. Array of image info on success
	 */
	public static function file_info( $file_id, $args = array() ) {
		if ( ! $path = get_attached_file( $file_id ) ) {
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
			$data['image'] = compact( 'src', 'width', 'height' );
			list( $src, $width, $height ) = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
			$data['thumb'] = compact( 'src', 'width', 'height' );
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
	 * Format a single value for the helper functions.
	 *
	 * @param array $field Field parameter
	 * @param array $value The value
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		$ids = implode( ',', wp_list_pluck( $value, 'ID' ) );

		return wp_playlist_shortcode( array(
			'ids'  => $ids,
			'type' => 'video',
		) );
	}

	/**
	 * Template for media item
	 * @return void
	 */
	public static function print_templates() {
		parent::print_templates();
		require_once( RWMB_INC_DIR . 'templates/video.php' );
	}
}
