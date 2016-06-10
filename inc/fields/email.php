<?php
/**
 * Email field class.
 */
class RWMB_Email_Field extends RWMB_Text_Field
{
	/**
	 * Sanitize email
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field )
	{
		return sanitize_email( $new );
	}
}
