<?php
defined( 'ABSPATH' ) || die;

/**
 * The secured password field.
 */
class RWMB_Password_Field extends RWMB_Input_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'rwmb-password', RWMB_CSS_URL . 'password.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-password', RWMB_JS_URL . 'password.js', [ 'jquery' ], RWMB_VER, true );
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
