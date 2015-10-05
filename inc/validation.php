<?php
/**
 * Validation module
 * @package Meta Box
 */

add_action( 'rwmb_after', 'rwmb_validation_rules' );

/**
 * Output validation rules of each meta box
 * The rules are outputted in [data-rules] attribute of an hidden div and will be converted into JSON by JS
 * @param RW_Meta_Box $object Meta Box object
 */
function rwmb_validation_rules( $object )
{
	if ( empty( $object->meta_box['validation'] ) )
	{
		return;
	}

	// Use script tag with type="text/html" to prevent browser to render
	echo '<script type="text/html" class="rwmb-validation-rules hidden" data-rules="' . esc_attr( json_encode( $object->meta_box['validation'] ) ) . '"></script>';
}

add_action( 'rwmb_enqueue_scripts', 'rwmb_validation_enqueue_scripts' );

/**
 * Enqueue scripts for validation
 * @param RW_Meta_Box $object Meta Box object
 */
function rwmb_validation_enqueue_scripts( $object )
{
	wp_enqueue_script( 'jquery-validate', RWMB_JS_URL . 'jquery.validate.min.js', array( 'jquery' ), RWMB_VER, true );
	wp_enqueue_script( 'rwmb-validate', RWMB_JS_URL . 'validate.js', array( 'jquery-validate' ), RWMB_VER, true );
	wp_localize_script( 'rwmb-validate', 'rwmbValidate', array(
		'summaryMessage' => 'Please correct the errors highlighted below and try again.',
	) );
}
