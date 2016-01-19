<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Image_Advanced_Field extends RWMB_Media_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field['mime_type'] = 'image';

		return $field;
	}
}
