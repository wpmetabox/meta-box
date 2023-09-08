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
	public function rules( RW_Meta_Box $meta_box_object ) {
		if ( empty( $meta_box_object->meta_box['validation'] ) ) {
			return;
		}

		// Get field ID prefix from the builder.
		$prefix = $meta_box_object->meta_box['prefix'] ?? '';

		// Add prefix for validation rules.
		$fields = $meta_box_object->meta_box['fields'];
		foreach ( $meta_box_object->meta_box['validation'] as &$rules ) {
			$rules = array_combine(
				array_map( function ( $key ) use ( $fields, $prefix ) {
					$id    = $prefix . $key;
					$index = array_search( $id, array_column( $fields, 'id' ), true );

					if ( $index === false ) {
						return $id;
					}

					if ( in_array( $fields[ $index ]['type'], [ 'file', 'image' ], true ) ) {
						return $fields[ $index ]['clone'] ? $fields[ $index ]['index_name'] : $fields[ $index ]['input_name'];
					}

					return $id;
				}, array_keys( $rules ) ),
				$rules
			);
		}

		echo '<script type="text/html" class="rwmb-validation" data-validation="' . esc_attr( wp_json_encode( $meta_box_object->meta_box['validation'] ) ) . '"></script>';
	}

	public function enqueue() {
		wp_enqueue_script( 'rwmb-validation', RWMB_JS_URL . 'validation.min.js', [ 'jquery', 'rwmb' ], RWMB_VER, true );

		$locale       = determine_locale();
		$locale_short = substr( $locale, 0, 2 );
		$locale       = file_exists( RWMB_DIR . "js/validation/i18n/messages_$locale.js" ) ? $locale : $locale_short;

		if ( file_exists( RWMB_DIR . "js/validation/i18n/messages_$locale.js" ) ) {
			wp_enqueue_script( 'rwmb-validation-i18n', RWMB_JS_URL . "validation/i18n/messages_$locale.js", [ 'rwmb-validation' ], RWMB_VER, true );
		}

		RWMB_Helpers_Field::localize_script_once( 'rwmb-validation', 'rwmbValidation', [
			'message' => esc_html( apply_filters( 'rwmb_validation_message_string', __( 'Please correct the errors highlighted below and try again.', 'meta-box' ) ) ),
		] );
	}
}
