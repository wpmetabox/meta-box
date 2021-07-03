<?php
/**
 * The secured password field.
 *
 * @package Meta Box
 */

/**
 * Password field class.
 */
class RWMB_Password_Field extends RWMB_Input_Field {
	/**
	 * Store secured password in the database.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = $new !== $old ? wp_hash_password( $new ) : $new;
		return $new;
	}
}
