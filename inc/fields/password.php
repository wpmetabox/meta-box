<?php
/**
 * The secured password field.
 */
class RWMB_Password_Field extends RWMB_Input_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-password-style', RWMB_CSS_URL . 'password.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-password-script', RWMB_JS_URL . 'password.js', array( 'jquery' ), RWMB_VER, true );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$field['append'] .= '<i class="password-icon show-icon"></i>';
		$output           = parent::html( $meta, $field );
		return $output;
	}

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
