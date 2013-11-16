<?php
add_filter( 'rwmb_meta_boxes', 'PREFIX_register_meta_box_oembed' );
function PREFIX_register_meta_box_oembed( $meta_boxes )
{
	$meta_boxes[] = array(
		'title' => __( 'oEmbed Demo', 'textdomain' ),
		'fields' => array(
			array(
				'id'       => 'oembed',
				'name'     => __( 'oEmbed(s)', 'rwmb' ),
				'type'     => 'oembed',

				// Allow to clone? Default is false
				'clone' => true,
			),
		),
	);
	return $meta_boxes;
}