<?php
add_filter( 'rwmb_meta_boxes', 'prefix_register_meta_box_url' );
function prefix_register_meta_box_url( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => __( 'URL Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'id'   => 'url',
				'name' => __( 'URL(s)', 'your-prefix' ),
				'type' => 'url',

				// Allow to clone? Default is false
				// 'clone' => true,
			),
		),
	);
	return $meta_boxes;
}
