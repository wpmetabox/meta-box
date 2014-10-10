<?php
add_filter( 'rwmb_meta_boxes', 'prefix_register_meta_box_oembed' );
function prefix_register_meta_box_oembed( $meta_boxes )
{
	$meta_boxes[] = array(
		'title' => __( 'oEmbed Demo', 'meta-box', 'meta-box' ),
		'fields' => array(
			array(
				'id'       => 'oembed',
				'name'     => __( 'oEmbed(s)', 'meta-box' ),
				'type'     => 'oembed',

				// Allow to clone? Default is false
				'clone' => true,
			),
		),
	);
	return $meta_boxes;
}