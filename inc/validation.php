<?php
/**
 * Validation module.
 */
class RWMB_Validation {
	public function __construct() {
		add_action( 'rwmb_after', [ $this, 'rules' ] );
		add_action( 'rwmb_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Output validation rules of each meta box.
	 * The rules are outputted in [data-validation] attribute of an hidden <script> and will be converted into JSON by JS.
	 */
	public function rules( RW_Meta_Box $object ) {
		if ( empty( $object->meta_box['validation'] ) ) {
			return;
		}

		if ( empty( $prefix ) ) {
			echo '<script type="text/html" class="rwmb-validation" data-validation="' . esc_attr( wp_json_encode( $object->meta_box['validation'] ) ) . '"></script>';
			return;
		}

		// Add prefix for validation
		$prefix = $object->meta_box['prefix'];

		foreach ( $object->meta_box['validation'] as &$rules ) {
			$rules = array_combine(
				array_map( function( $key ) use ( $prefix ) {
					return $prefix . $key;
				}, array_keys( $rules )),
				$rules
			);
		}
		echo '<script type="text/html" class="rwmb-validation" data-validation="' . esc_attr( wp_json_encode( $object->meta_box['validation'] ) ) . '"></script>';
	}

	public function enqueue() {
		wp_enqueue_script( 'rwmb-validation', RWMB_JS_URL . 'validation.min.js', [ 'jquery', 'rwmb' ], RWMB_VER, true );

		RWMB_Helpers_Field::localize_script_once( 'rwmb-validation', 'rwmbValidation', [
			'message' => esc_html( apply_filters( 'rwmb_validation_message_string', __( 'Please correct the errors highlighted below and try again.', 'meta-box' ) ) ),
		] );
	}
}
