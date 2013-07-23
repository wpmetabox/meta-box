<?php
/**
 * This file contains all helpers/public functions
 * that can be used both on the back-end or front-end
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

add_shortcode( 'rwmb_meta', 'rwmb_meta_shortcode' );

/**
 * Shortcode to display meta value
 *
 * @param $atts Array of shortcode attributes, same as rwmb_meta function, but has more "meta_key" parameter
 * @see rwmb_meta function below
 *
 * @return string
 */
function rwmb_meta_shortcode( $atts )
{
	$atts = wp_parse_args( $atts, array(
		'type'    => 'text',
		'post_id' => get_the_ID(),
	) );
	if ( empty( $atts['meta_key'] ) )
		return '';

	$meta = rwmb_meta( $atts['meta_key'], $atts, $atts['post_id'] );

	// Get uploaded files info
	if ( in_array( $atts['type'], array( 'file', 'file_advanced' ) ) )
	{
		$content = '<ul>';
		foreach ( $meta as $file )
		{
			$content .= sprintf(
				'<li><a href="%s" title="%s">%s</a></li>',
				$file['url'],
				$file['title'],
				$file['name']
			);
		}
		$content .= '</ul>';
	}

	// Get uploaded images info
	elseif ( in_array( $atts['type'], array( 'image', 'plupload_image', 'thickbox_image', 'image_advanced' ) ) )
	{
		$content = '<ul>';
		foreach ( $meta as $image )
		{
			// Link thumbnail to full size image?
			if ( isset( $atts['link'] ) && $atts['link'] )
			{
				$content .= sprintf(
					'<li><a href="%s" title="%s"><img src="%s" alt="%s" title="%s" /></a></li>',
					$image['full_url'],
					$image['title'],
					$image['url'],
					$image['alt'],
					$image['title']
				);
			}
			else
			{
				$content .= sprintf(
					'<li><img src="%s" alt="%s" title="%s" /></li>',
					$image['url'],
					$image['alt'],
					$image['title']
				);
			}
		}
		$content .= '</ul>';
	}

	// Get post terms
	elseif ( 'taxonomy' == $atts['type'] )
	{
		$content = '<ul>';
		foreach ( $meta as $term )
		{
			$content .= sprintf(
				'<li><a href="%s" title="%s">%s</a></li>',
				get_term_link( $term, $atts['taxonomy'] ),
				$term->name,
				$term->name
			);
		}
		$content .= '</ul>';
	}

	// Normal multiple fields: checkbox_list, select with multiple values
	elseif ( is_array( $meta ) )
	{
		$content = '<ul><li>' . implode( '</li><li>', $meta ) . '</li></ul>';
	}

	else
	{
		$content = $meta;
	}

	return apply_filters( __FUNCTION__, $content );
}

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
	$post_id = empty( $post_id ) ? get_the_ID() : $post_id;

	$args = wp_parse_args( $args, array(
		'type' => 'text',
	) );

	// Set 'multiple' for fields based on 'type'
	$args['multiple'] = in_array( $args['type'], array( 'checkbox_list', 'file', 'file_advanced', 'image', 'image_advanced', 'plupload_image', 'thickbox_image' ) );

	$meta = get_post_meta( $post_id, $key, !$args['multiple'] );

	// Get uploaded files info
	if ( in_array( $args['type'], array( 'file', 'file_advanced' ) ) )
	{
		if ( is_array( $meta ) && !empty( $meta ) )
		{
			$files = array();
			foreach ( $meta as $id )
			{
				$files[$id] = rwmb_file_info( $id );
			}
			$meta = $files;
		}
	}

	// Get uploaded images info
	elseif ( in_array( $args['type'], array( 'image', 'plupload_image', 'thickbox_image', 'image_advanced' ) ) )
	{
		if ( is_array( $meta ) && !empty( $meta ) )
		{
			global $wpdb;
			$meta = implode( ',', $meta );

			// Re-arrange images with 'menu_order'
			$meta = $wpdb->get_col( "
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND ID in ({$meta})
				ORDER BY menu_order ASC
			" );

			$images = array();
			foreach ( $meta as $id )
			{
				$images[$id] = rwmb_image_info( $id, $args );
			}
			$meta = $images;
		}
	}

	// Get terms
	elseif ( 'taxonomy_advanced' == $args['type'] )
	{
		if ( !empty( $args['taxonomy'] ) )
		{
			$term_ids = array_map( 'intval', array_filter( explode( ',', $meta . ',' ) ) );

			$meta = array();
			foreach ( $term_ids as $term_id )
			{
				$meta[] = get_term( $term_id, $args['taxonomy'] );
			}
		}
		else
		{
			$meta = array();
		}
	}

	// Get post terms
	elseif ( 'taxonomy' == $args['type'] )
	{
		$meta = empty( $args['taxonomy'] ) ? array() : wp_get_post_terms( $post_id, $args['taxonomy'] );
	}

	// Get map
	elseif ( 'map' == $args['type'] )
	{
		$meta = rwmb_meta_map( $key, $args, $post_id );
	}

	return apply_filters( __FUNCTION__, $meta, $key, $args, $post_id );
}

/**
 * Get uploaded file information
 *
 * @param int $id Attachment file ID (post ID). Required.
 *
 * @return array|bool False if file not found. Array of (id, name, path, url) on success
 */
function rwmb_file_info( $id )
{
	$path = get_attached_file( $id );
	return array(
		'ID'    => $id,
		'name'  => basename( $path ),
		'path'  => $path,
		'url'   => wp_get_attachment_url( $id ),
		'title' => get_the_title( $id ),
	);
}

/**
 * Get uploaded image information
 *
 * @param int   $id   Attachment image ID (post ID). Required.
 * @param array $args Array of arguments (for size). Required.
 *
 * @return array|bool False if file not found. Array of (id, name, path, url) on success
 */
function rwmb_image_info( $id, $args = array() )
{
	$args = wp_parse_args( $args, array(
		'size' => 'thumbnail',
	) );

	$img_src = wp_get_attachment_image_src( $id, $args['size'] );
	if ( empty( $img_src ) )
		return false;

	$attachment = &get_post( $id );
	$path = get_attached_file( $id );
	return array(
		'ID'          => $id,
		'name'        => basename( $path ),
		'path'        => $path,
		'url'         => $img_src[0],
		'width'       => $img_src[1],
		'height'      => $img_src[2],
		'full_url'    => wp_get_attachment_url( $id ),
		'title'       => $attachment->post_title,
		'caption'     => $attachment->post_excerpt,
		'description' => $attachment->post_content,
		'alt'         => get_post_meta( $id, '_wp_attachment_image_alt', true ),
	);
}

/**
 * Display map using Google API
 *
 * @param  string   $key     Meta key
 * @param  array    $args    Map parameter
 * @param  int|null $post_id Post ID
 *
 * @return string
 */
function rwmb_meta_map( $key, $args = array(), $post_id = null )
{
	$post_id = empty( $post_id ) ? get_the_ID() : $post_id;
	$loc = get_post_meta( $post_id, $key, true );
	if ( !$loc )
		return '';

	$parts = array_map( 'trim', explode( ',', $loc ) );

	// No zoom entered, set it to 14 by default
	if ( count( $parts ) < 3 )
		$parts[2] = 14;

	// Map parameters
	$args = wp_parse_args( $args, array(
		'width'        => 640,
		'height'       => 480,
		'zoom'         => $parts[2], // Default to 'zoom' level set in admin, but can be overwritten
		'marker'       => true,      // Display marker?
		'marker_title' => '',        // Marker title, when hover
		'info_window'  => '',        // Content of info window (when click on marker). HTML allowed
	) );

	// Counter to display multiple maps on same page
	static $counter = 0;

	$html = sprintf(
		'<div id="rwmb-map-canvas-%d" style="width:%s;height:%s"></div>',
		$counter,
		$args['width'] . 'px',
		$args['height'] . 'px'
	);
	$html .= '<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>';
	$html .= '<script>
		(function()
		{
			function initialize()
			{
	';

	$html .= sprintf( '
		var center = new google.maps.LatLng( %s, %s ),
			mapOptions = {
				center: center,
				zoom: %d,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			},
			map = new google.maps.Map( document.getElementById( "rwmb-map-canvas-%d" ), mapOptions );',
		$parts[0], $parts[1],
		$args['zoom'],
		$counter
	);

	if ( $args['marker'] )
	{
		$html .= sprintf( '
			var marker = new google.maps.Marker( {
				position: center,
				map: map%s
			} );',
			$args['marker_title'] ? ', title: "' . $args['marker_title'] . '"' : ''
		);

		if ( $args['info_window'] )
		{
			$html .= sprintf( '
				var infoWindow = new google.maps.InfoWindow( {
					content: "%s"
				} );

				google.maps.event.addListener( marker, "click", function()
				{
					infoWindow.open( map, marker );
				} );',
				$args['info_window']
			);
		}
	}

	$html .= '
			}
			google.maps.event.addDomListener(window, "load", initialize);
		}());
		</script>';

	$counter++;
	return $html;
}
