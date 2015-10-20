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
			$field             = parent::normalize_field( $field );			
			$field['mime_type'] = 'image';			

			return $field;
		}
	}
}
