<?php
/**
 * Validation module.
 *
 * @package Meta Box
 */

/**
 * Validation class.
 */
class RWMB_Validation {

	/**
	 * Add hooks when module is loaded.
	 */
	public function __construct() {
		add_action( 'rwmb_after', array( $this, 'rules' ) );
		add_action( 'rwmb_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Output validation rules of each meta box.
	 * The rules are outputted in [data-validation] attribute of an hidden <script> and will be converted into JSON by JS.
	 *
	 * @param RW_Meta_Box $object Meta Box object.
	 */
	public function rules( RW_Meta_Box $object ) {
		if ( ! empty( $object->meta_box['validation'] ) ) {
			echo '<script type="text/html" class="rwmb-validation" data-validation="' . esc_attr( wp_json_encode( $object->meta_box['validation'] ) ) . '"></script>';
		}
	}

	/**
	 * Enqueue scripts for validation.
	 *
	 * @param RW_Meta_Box $object Meta Box object.
	 */
	public function enqueue( RW_Meta_Box $object ) {
		wp_enqueue_script( 'jquery-validation', RWMB_JS_URL . 'jquery-validation/jquery-validation.min.js', array( 'jquery' ), '1.19.1', true );
		wp_enqueue_script( 'rwmb-validation', RWMB_JS_URL . 'validation.min.js', array( 'jquery-validation', 'rwmb' ), RWMB_VER, true );

		RWMB_Helpers_Field::localize_script_once(
			'rwmb-validation',
			'rwmbValidation',
			array(
				'message' => esc_html__( 'Please correct the errors highlighted below and try again.', 'meta-box' ),
			)
		);
	}
}
