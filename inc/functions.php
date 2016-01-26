<?php
/**
 * Plugin public functions.
 */

/**
 * Get post meta
 *
 * @param string   $key     Meta key. Required.
 * @param int|null $post_id Post ID. null for current post. Optional
 * @param array    $args    Array of arguments. Optional.
 *
 * @return mixed
 */
function rwmb_meta( $key, $args = array(), $post_id = null )
{
	return RWMB_Helper::meta( $key, $args, $post_id );
}

/**
 * Get value of custom field.
 * This is used to replace old version of rwmb_meta key.
 *
 * @param  string   $field_id Field ID. Required.
 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details
 * @param  int|null $post_id  Post ID. null for current post. Optional.
 *
 * @return mixed false if field doesn't exist. Field value otherwise.
 */
function rwmb_get_field( $field_id, $args = array(), $post_id = null )
{
	$field = RWMB_Helper::find_field( $field_id );

	// Get field value
	$value = $field ? call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'get_value' ), $field, $args, $post_id ) : false;

	/**
	 * Allow developers to change the returned value of field
	 *
	 * @param mixed    $value   Field value
	 * @param array    $field   Field parameter
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 */
	$value = apply_filters( 'rwmb_get_field', $value, $field, $args, $post_id );

	return $value;
}

/**
 * Display the value of a field
 *
 * @param  string   $field_id Field ID. Required.
 * @param  array    $args     Additional arguments. Rarely used. See specific fields for details
 * @param  int|null $post_id  Post ID. null for current post. Optional.
 * @param  bool     $echo     Display field meta value? Default `true` which works in almost all cases. We use `false` for  the [rwmb_meta] shortcode
 *
 * @return string
 */
function rwmb_the_field( $field_id, $args = array(), $post_id = null, $echo = true )
{
	// Find field
	$field = RWMB_Helper::find_field( $field_id );

	if ( ! $field )
		return '';

	$output = call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'the_value' ), $field, $args, $post_id );

	/**
	 * Allow developers to change the returned value of field
	 *
	 * @param mixed    $value   Field HTML output
	 * @param array    $field   Field parameter
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 */
	$output = apply_filters( 'rwmb_the_field', $output, $field, $args, $post_id );

	if ( $echo )
		echo $output;

	return $output;
}

/**
 * Shortcode to display meta value
 *
 * @param array $atts Shortcode attributes, same as meta() function, but has more "meta_key" parameter
 *
 * @see meta() function below
 *
 * @return string
 */
function rwmb_meta_shortcode( $atts )
{
	$atts = wp_parse_args( $atts, array(
		'post_id' => get_the_ID(),
	) );
	if ( empty( $atts['meta_key'] ) )
		return '';

	$field_id = $atts['meta_key'];
	$post_id  = $atts['post_id'];
	unset( $atts['meta_key'], $atts['post_id'] );

	return rwmb_the_field( $field_id, $atts, $post_id, false );
}

add_shortcode( 'rwmb_meta', 'rwmb_meta_shortcode' );
