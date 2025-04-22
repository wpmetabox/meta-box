<?php
defined( 'ABSPATH' ) || die;

/**
 * The secured password field.
 */
class RWMB_Password_Field extends RWMB_Input_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-password', RWMB_CSS_URL . 'password.css', [], RWMB_VER );
		wp_style_add_data( 'rwmb-password', 'path', RWMB_CSS_DIR . 'password.css' );
		wp_enqueue_script( 'rwmb-password', RWMB_JS_URL . 'password.js', [], RWMB_VER, true );
	}

	public static function html( $meta, $field ) {
		$output = parent::html( $meta, $field );

		// Skip password toggle if field has append
		if ( $field['append'] ) {
			return $output;
		}

		// Add password toggle button
		$button = '<button type="button" class="rwmb-password-toggle" >
				<svg class="rwmb-eye-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
                <svg class="rwmb-eye-off-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="display: none;">
            		<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
        		</svg>
            </button>';

		$output .= $button;

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
