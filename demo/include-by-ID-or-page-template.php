<?php
$prefix = 'rw_';

global $meta_boxes;
$meta_boxes   = array();
$meta_boxes[] = array(
	'id'     => 'any_id',
	'title'  => __( 'Meta Box Title', 'rwmb' ),
	'pages'  => array( 'post' ),
	'fields' => array(

		// IMAGE UPLOAD
		array(
			'name' => __( 'Your images', 'rwmb' ),
			'id'   => "{$prefix}img",
			'type' => 'plupload_image',
		),
	),
);

/**
 * Register meta boxes
 *
 * @return void
 */
function rw_register_meta_boxes()
{
	global $meta_boxes;

	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( ! class_exists( 'RW_Meta_Box' ) )
		return;

	// Register meta boxes only for some posts/pages
	if ( ! rw_maybe_include() )
		return;

	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}

add_action( 'admin_init', 'rw_register_meta_boxes' );

/**
 * Check if meta boxes is included
 *
 * @return bool
 */
function rw_maybe_include()
{
	// Include in back-end only
	if ( ! defined( 'WP_ADMIN' ) || ! WP_ADMIN )
		return false;

	// Always include for ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return true;

	// Check for post IDs
	$checked_post_IDs = array( 61, 63, 65, 67, 2 );

	if ( isset( $_GET['post'] ) )
		$post_id = intval( $_GET['post'] );
	elseif ( isset( $_POST['post_ID'] ) )
		$post_id = intval( $_POST['post_ID'] );
	else
		$post_id = false;

	$post_id = (int) $post_id;

	if ( in_array( $post_id, $checked_post_IDs ) )
		return true;

	// Check for page template
	$checked_templates = array( 'full-width.php', 'sidebar-page.php' );

	$template = get_post_meta( $post_id, '_wp_page_template', true );
	if ( in_array( $template, $checked_templates ) )
		return true;

	// If no condition matched
	return false;
}
