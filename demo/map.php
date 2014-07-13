<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_register_meta_boxes' );
function your_prefix_register_meta_boxes( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Google Map', 'rwmb' ),
		'fields' => array(
			array(
				'id'            => 'address',
				'name'          => __( 'Address', 'rwmb' ),
				'type'          => 'text',
				'std'           => __( 'Hanoi, Vietnam', 'rwmb' ),
			),
			array(
				'id'            => 'loc',
				'name'          => __( 'Location', 'rwmb' ),
				'type'          => 'map',
				'std'           => '-6.233406,-35.049906,15',     // 'latitude,longitude[,zoom]' (zoom is optional)
				'style'         => 'width: 500px; height: 500px',
				'address_field' => 'address',                     // Name of text field where address is entered. Can be list of text fields, separated by commas (for ex. city, state)
			),
		),
	);

	return $meta_boxes;
}
