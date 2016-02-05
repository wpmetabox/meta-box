<?php
/**
 * HTML5 URL field class.
 */
class RWMB_URL_Field extends RWMB_Text_Field
{
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['type'] = 'url';

		return $attributes;
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
