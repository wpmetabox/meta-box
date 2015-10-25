<?php
/**
 * Validation module
 * @package Meta Box
 */

/**
 * Validation class
 */
class RWMB_Validation
{
	/**
	 * Add hooks when module is loaded
	 */
	public static function load()
	{
		add_action( 'rwmb_after', array( __CLASS__, 'rules' ) );
		add_action( 'rwmb_enqueue_scripts', array( __CLASS__, 'scripts' ) );
	}

	/**
	 * Output validation rules of each meta box
	 * The rules are outputted in [data-rules] attribute of an hidden div and will be converted into JSON by JS
	 * @param RW_Meta_Box $object Meta Box object
	 */
	public static function rules( RW_Meta_Box $object )
	{
		if ( ! empty( $object->meta_box['validation'] ) )
		{
			// Use script tag with type="text/html" to prevent browser to render
			echo '<script type="text/html" class="rwmb-validation-rules hidden" data-rules="' . esc_attr( json_encode( $object->meta_box['validation'] ) ) . '"></script>';
		}
	}

	/**
	 * Enqueue scripts for validation
	 */
	public static function scripts()
	{
		wp_enqueue_script( 'jquery-validate', RWMB_JS_URL . 'jquery.validate.min.js', array( 'jquery' ), RWMB_VER, true );
		wp_enqueue_script( 'rwmb-validate', RWMB_JS_URL . 'validate.js', array( 'jquery-validate' ), RWMB_VER, true );
		wp_localize_script( 'rwmb-validate', 'rwmbValidate', array(
			'summaryMessage' => __( 'Please correct the errors highlighted below and try again.', 'meta-box' ),
		) );
	}
}

RWMB_Validation::load();
