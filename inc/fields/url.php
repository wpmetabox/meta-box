<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_URL_Field extends RWMB_Text_Field
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

		$field['attributes']['type'] = 'url';

		return $field;
	}

	/**
	 * Sanitize url
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return string
	 */
	static function value( $new, $old, $post_id, $field )
	{
		return is_array( $new ) ? array_map( 'esc_url_raw', $new ) : esc_url_raw( $new );
	}
}
