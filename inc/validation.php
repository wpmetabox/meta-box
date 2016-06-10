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
	public function rules( RW_Meta_Box $object )
	{
		if ( ! empty( $object->meta_box['validation'] ) )
		{
			echo '<script type="text/html" class="rwmb-validation-rules" data-rules="' . esc_attr( json_encode( $object->meta_box['validation'] ) ) . '"></script>';
		}
	}

	/**
	 * Enqueue scripts for validation.
	 */
	public function enqueue()
	{
		wp_enqueue_script( 'jquery-validate', RWMB_JS_URL . 'jquery.validate.min.js', array( 'jquery' ), RWMB_VER, true );
		wp_enqueue_script( 'rwmb-validate', RWMB_JS_URL . 'validate.js', array( 'jquery-validate' ), RWMB_VER, true );
		/**
		 * Prevent loading localized string twice.
		 * @link https://github.com/rilwis/meta-box/issues/850
		 */
		$wp_scripts = wp_scripts();
		if ( ! $wp_scripts->get_data( 'rwmb-validate', 'data' ) )
		{
			wp_localize_script( 'rwmb-validate', 'rwmbValidate', array(
				'summaryMessage' => __( 'Please correct the errors highlighted below and try again.', 'meta-box' ),
			) );
		}
	}
}
