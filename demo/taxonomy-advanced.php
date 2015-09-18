<?php
/**
 * This file demonstrates how to use 'taxonomy_advanced' field
 */
add_filter( 'rwmb_meta_boxes', 'your_prefix_taxonomy_advanced_demo' );
function your_prefix_taxonomy_advanced_demo( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Taxonomy_Advanced Demo', 'your-prefix' ),
		'fields' => array(
			array(
				'name'    => __( 'Taxonomy', 'your-prefix' ),
				'id'      => 'prefix_taxonomy_advanced',
				'type'    => 'taxonomy_advanced',

				'options' => array(
					// Taxonomy name
					'taxonomy' => 'category',

					// How to show taxonomy: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree', select_advanced or 'select'. Optional
					'type'     => 'select_tree',

					// Additional arguments for get_terms() function. Optional
					'args'     => array()
				),
			),
		),
	);
	return $meta_boxes;
}
