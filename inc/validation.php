<?php
/**
 * Validation module.
 * @package Meta Box
 */

/**
 * Validation class.
 */
class RWMB_Validation
{
	/**
	 * Add hooks when module is loaded.
	 */
	public function __construct()
	{
		add_action( 'rwmb_after', array( $this, 'rules' ) );
		add_action( 'rwmb_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Output validation rules of each meta box.
	 * The rules are outputted in [data-rules] attribute of an hidden <script> and will be converted into JSON by JS.
	 * @param RW_Meta_Box $object Meta Box object
	 */
	public function rules( $object )
	{
		if ( ! empty( $object->meta_box['validation'] ) )
		{
			echo '<script type="text/html" class="rwmb-validation-rules" data-rules="' . esc_attr( json_encode( $object->meta_box['validation'] ) ) . '"></script>';
		}
	}

	/**
	 * Enqueue scripts for validation.
	 * @param RW_Meta_Box $object Meta Box object
	 */
	public function enqueue( $object )
	{
		if ( empty( $object->meta_box['validation'] ) )
		{
			return;
		}
		wp_enqueue_script( 'jquery-validation', RWMB_JS_URL . 'jquery-validation/jquery.validate.min.js', array( 'jquery' ), '1.15.0', true );
		wp_enqueue_script( 'jquery-validation-additional-methods', RWMB_JS_URL . 'jquery-validation/additional-methods.min.js', array( 'jquery-validation' ), '1.15.0', true );
		wp_enqueue_script( 'rwmb-validate', RWMB_JS_URL . 'validate.js', array( 'jquery-validation', 'jquery-validation-additional-methods' ), RWMB_VER, true );

		/*
		 * Prevent loading localized string twice.
		 * @link https://github.com/rilwis/meta-box/issues/850
		 */
		$wp_scripts = wp_scripts();
		if ( ! $wp_scripts->get_data( 'rwmb-validate', 'data' ) )
		{
			wp_localize_script( 'rwmb-validate', 'rwmbValidate', array(
				'summaryMessage' => esc_html__( 'Please correct the errors highlighted below and try again.', 'meta-box' ),
			) );
		}
	}
}
