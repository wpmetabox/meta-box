<?php
add_action( 'admin_init', 'rwmb_register_meta_boxes' );

/**
 * Register meta boxes via a filter
 * Advantages:
 * - prevents incorrect hook
 * - prevents duplicated global variables
 * - allows users to remove/hide registered meta boxes
 * - no need to check for class existences
 *
 * @return void
 */
function rwmb_register_meta_boxes()
{
	$meta_boxes = apply_filters( 'rwmb_meta_boxes', array() );

	// Prevent errors showing if invalid value is returned from the filter above
	if ( empty( $meta_boxes ) || ! is_array( $meta_boxes ) )
		return;

	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}
}

/**
 * Normalize parameters of meta boxes and fields AFTER all meta boxes are registered
 * Priority 1000 makes sure this callback performs after all meta boxes are registered
 */
add_filter( 'rwmb_meta_boxes', 'rwmb_normalize', 1000 );

/**
 * Normalize parameters of meta boxes and fields
 *
 * @use RW_Meta_Box::normalize()
 *
 * @param array $meta_boxes Registered meta boxes
 *
 * @return array
 */
function rwmb_normalize( $meta_boxes )
{
	// Call normalize helper function in RW_Meta_Box class
	return array_map( array( 'RW_Meta_Box', 'normalize' ), $meta_boxes );
}

add_action( 'edit_page_form', 'rwmb_fix_page_template' );

/**
 * WordPress will prevent post data saving if a page template has been selected that does not exist
 * This is especially a problem when switching to our theme, and old page templates are in the post data
 * Unset the page template if the page does not exist to allow the post to save
 *
 * @param WP_Post $post
 *
 * @return void
 * @since 4.3.10
 */
function rwmb_fix_page_template( $post )
{
	$template       = get_post_meta( $post->ID, '_wp_page_template', true );
	$page_templates = wp_get_theme()->get_page_templates();

	// If the template doesn't exists, remove the data to allow WordPress to save
	if ( ! isset( $page_templates[$template] ) )
		delete_post_meta( $post->ID, '_wp_page_template' );
}
