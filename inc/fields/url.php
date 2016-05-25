<?php
/**
 * HTML5 URL field class.
 */
class RWMB_URL_Field extends RWMB_Text_Field
{
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
