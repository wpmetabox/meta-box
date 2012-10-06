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
			$i18n_title = _x( 'Upload images', 'image upload', 'rwmb' );

			$html  = wp_nonce_field( "rwmb-delete-file_{$field['id']}", "nonce-delete-file_{$field['id']}", false, false );
			$html .= wp_nonce_field( "rwmb-reorder-images_{$field['id']}", "nonce-reorder-images_{$field['id']}", false, false );
			$html .= "<input type='hidden' class='field-id' value='{$field['id']}' />";

			// Uploaded images
			$html .= self::get_uploaded_images( $meta );

			// Show form upload
			$html .= "<a href='#' class='button rwmb-thickbox-upload' rel='{$field['id']}'>{$i18n_title}</a>";

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
			if ( ! is_array( $new ) || empty( $new ) )
				return;

			$name   = $field['id'];
			$images = (array) get_post_meta( $post_id, $name, false );
			foreach ( $new as $add_new )
			{
				if ( ! in_array( $add_new, $images ) )
					add_post_meta( $post_id, $name, $add_new, false );
			}
		}
	}
}