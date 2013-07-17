<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

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
			wp_enqueue_script( 'media-upload' );

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
			$i18n_title = apply_filters( 'rwmb_thickbox_image_upload_string', _x( 'Upload Images', 'image upload', 'rwmb' ), $field );

			// Uploaded images
			$html = self::get_uploaded_images( $meta, $field );

			// Show form upload
			$html .= "<a href='#' class='button rwmb-thickbox-upload' data-field_id='{$field['id']}'>{$i18n_title}</a>";

			return $html;
		}

		/**
		 * Get field value
		 * It's the combination of new (uploaded) images and saved images
		 *
		 * @param array $new
		 * @param array $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			return array_unique( array_merge( $old, $new ) );
		}
	}
}
