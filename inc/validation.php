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
	public function rules( RW_Meta_Box $meta_box ) {
		$settings = $meta_box->meta_box;
		if ( empty( $settings['validation'] ) ) {
			return;
		}

		$prefix     = $settings['prefix'] ?? ''; // Get field ID prefix from the builder.
		$fields     = $settings['fields'];
		$validation = $settings['validation'];
		$ids        = wp_list_pluck( $fields, 'id' ); // Don't use array_column() as it doesn't preserve keys.

		// Add prefix for validation rules.
		foreach ( $validation as &$rules ) {
			$rules = array_combine(
				array_map( function ( $key ) use ( $fields, $prefix, $ids ) {
					$id    = $prefix . $key;
					$index = array_search( $id, $ids, true );

					if ( $index === false ) {
						return $id;
					}

					$field = $fields[ $index ];

					if ( in_array( $field['type'], [ 'file', 'image' ], true ) ) {
						return $field['clone'] ? $field['index_name'] : $field['input_name'];
					}

					return $id;
				}, array_keys( $rules ) ),
				$rules
			);
		}

		echo '<script type="text/html" class="rwmb-validation" data-validation="' . esc_attr( wp_json_encode( $validation ) ) . '"></script>';
	}

	public function enqueue() {
		wp_enqueue_script( 'jquery-validation', RWMB_JS_URL . 'validation/jquery.validate.js', [ 'jquery' ], '1.20.0', true );
		wp_enqueue_script( 'jquery-validation-additional-methods', RWMB_JS_URL . 'validation/additional-methods.js', [ 'jquery-validation' ], '1.20.0', true );
		wp_enqueue_script( 'rwmb-validation', RWMB_JS_URL . 'validation/validation.js', [ 'jquery-validation-additional-methods', 'rwmb' ], RWMB_VER, true );

		$locale       = determine_locale();
		$locale_short = substr( $locale, 0, 2 );
		$locale       = file_exists( RWMB_DIR . "js/validation/i18n/messages_$locale.js" ) ? $locale : $locale_short;

		if ( file_exists( RWMB_DIR . "js/validation/i18n/messages_$locale.js" ) ) {
			wp_enqueue_script( 'jquery-validation-i18n', RWMB_JS_URL . "validation/i18n/messages_$locale.js", [ 'jquery-validation-additional-methods' ], '1.20.0', true );
		}

		RWMB_Helpers_Field::localize_script_once( 'rwmb-validation', 'rwmbValidation', [
			'message' => esc_html( apply_filters( 'rwmb_validation_message_string', __( 'Please correct the errors highlighted below and try again.', 'meta-box' ) ) ),
		] );
	}
}
