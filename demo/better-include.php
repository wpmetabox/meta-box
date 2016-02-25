<?php
/**
 * This file show you an improvement of better include meta box in some pages
 * based on post ID, post slug, page template and page parent
 *
 * This is created, maintained and supported by the community
 * Use it with your own risk
 *
 * For more advanced and OFFICIAL support, check out the extension https://metabox.io/plugins/meta-box-include-exclude/
 *
 * @author Charlie Rosenbury <charlie@40digits.com>
 */

add_filter( 'rwmb_meta_boxes', 'YOURPREFIX_register_meta_boxes' );

/**
 * Register meta boxes
 * @param $meta_boxes
 * @return array
 */
function YOURPREFIX_register_meta_boxes( $meta_boxes )
{
	$prefix       = 'rw_';
	$meta_boxes[] = array(
		'title'   => __( 'Meta Box Title', 'your-prefix' ),
		'fields'  => array(
			array(
				'name' => __( 'Your images', 'your-prefix' ),
				'id'   => "{$prefix}img",
				'type' => 'plupload_image',
			),
		),
		'only_on' => array(
			'id'       => array( 1, 2 ),
			// 'slug'  => array( 'news', 'blog' ),
			'template' => array( 'fullwidth.php', 'simple.php' ),
			'parent'   => array( 10 ),
		),
	);

	foreach ( $meta_boxes as $k => $meta_box )
	{
		if ( isset( $meta_box['only_on'] ) && ! rw_maybe_include( $meta_box['only_on'] ) )
		{
			unset( $meta_boxes[$k] );
		}
	}

	return $meta_boxes;
}

/**
 * Check if meta boxes is included
 *
 * @return bool
 */
function rw_maybe_include( $conditions )
{
	// Always include in the frontend to make helper function work
	if ( ! is_admin() )
	{
		return true;
	}

	// Always include for ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
	{
		return true;
	}

	if ( isset( $_GET['post'] ) )
	{
		$post_id = intval( $_GET['post'] );
	}
	elseif ( isset( $_POST['post_ID'] ) )
	{
		$post_id = intval( $_POST['post_ID'] );
	}
	else
	{
		$post_id = false;
	}

	$post_id = (int) $post_id;
	$post    = get_post( $post_id );

	foreach ( $conditions as $cond => $v )
	{
		// Catch non-arrays too
		if ( ! is_array( $v ) )
		{
			$v = array( $v );
		}

		switch ( $cond )
		{
			case 'id':
				if ( in_array( $post_id, $v ) )
				{
					return true;
				}
				break;
			case 'parent':
				$post_parent = $post->post_parent;
				if ( in_array( $post_parent, $v ) )
				{
					return true;
				}
				break;
			case 'slug':
				$post_slug = $post->post_name;
				if ( in_array( $post_slug, $v ) )
				{
					return true;
				}
				break;
			case 'category': //post must be saved or published first
				$categories = get_the_category( $post->ID );
				$catslugs   = array();
				foreach ( $categories as $category )
				{
					array_push( $catslugs, $category->slug );
				}
				if ( array_intersect( $catslugs, $v ) )
				{
					return true;
				}
				break;
			case 'template':
				$template = get_post_meta( $post_id, '_wp_page_template', true );
				if ( in_array( $template, $v ) )
				{
					return true;
				}
				break;
		}
	}

	// If no condition matched
	return false;
}
