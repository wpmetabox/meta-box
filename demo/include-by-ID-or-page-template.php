<?php
/**
 * This demo shows how to register meta boxes for a page by ID or page template
 * This is created, maintained and supported by the community
 * Use it with your own risk
 *
 * For more advanced and OFFICIAL support, check out the extension https://metabox.io/plugins/meta-box-include-exclude/
 */

add_filter( 'rwmb_meta_boxes', 'YOURPREFIX_register_meta_boxes' );

/**
 * Register meta boxes
 *
 * @param $meta_boxes
 * @return array
 */
function YOURPREFIX_register_meta_boxes( $meta_boxes ) {
	// Check before register meta boxes
	if ( ! rw_maybe_include() ) {
		return $meta_boxes;
	}

	// Register meta boxes
	// @see https://metabox.io/docs/registering-meta-boxes/
	$prefix       = 'rw_';
	$meta_boxes[] = array(
		'id'         => 'any_id',
		'title'      => __( 'Meta Box Title', 'your-prefix' ),
		'post_types' => 'page',
		'fields'     => array(
			array(
				'name' => __( 'Your images', 'your-prefix' ),
				'id'   => "{$prefix}img",
				'type' => 'plupload_image',
			),
		),
	);

	return $meta_boxes;
}

/**
 * Check if meta boxes is included
 *
 * @return bool
 */
function rw_maybe_include() {
	// Always include in the frontend to make helper function work
	if ( ! is_admin() ) {
		return true;
	}

	// Always include for ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return true;
	}

	// Check for post IDs
	$checked_post_IDs = array( 61, 63, 65, 67, 2 );

	if ( isset( $_GET['post'] ) ) {
		$post_id = intval( $_GET['post'] );
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = intval( $_POST['post_ID'] );
	} else { $post_id = false;
	}

	$post_id = (int) $post_id;

	if ( in_array( $post_id, $checked_post_IDs ) ) {
		return true;
	}

	// Check for page template
	$checked_templates = array( 'full-width.php', 'sidebar-page.php' );

	$template = get_post_meta( $post_id, '_wp_page_template', true );
	if ( in_array( $template, $checked_templates ) ) {
		return true;
	}

	// If no condition matched
	return false;
}
