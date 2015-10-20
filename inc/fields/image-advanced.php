<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

require_once RWMB_FIELDS_DIR . 'media.php';
if ( ! class_exists( 'RWMB_Image_Advanced_Field' ) )
{
	class RWMB_Image_Advanced_Field extends RWMB_Media_Field
	{
		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = parent::normalize_field( $field );			
			$field['mime_type'] = 'image';			

			return $field;
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
			$i18n_add    = apply_filters( 'rwmb_media_add_string', _x( '+ Add Media', 'media', 'meta-box' ) );
			$meta = (array) $meta;
			$meta = implode( ',', $meta );
			$html = sprintf(
				'<input type="hidden" name="%s" value="%s" class="rwmb-image-advanced">
				<div class="rwmb-media-view"  data-mime-type="%s" data-max-files="%s" data-force-delete="%s"></div>',
				$field['field_name'],
				esc_attr( $meta ),
				$field['mime_type'],
				$field['max_file_uploads'] ,
				$field['force_delete'] ? 'true' : 'false',
				$i18n_add
			);

			return $html;
		}
	}
}
