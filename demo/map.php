<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_register_meta_boxes' );
function your_prefix_register_meta_boxes( $meta_boxes )
{
	$meta_boxes[] = array(
		'title'  => __( 'Google Map', 'meta-box' ),
		'fields' => array(
			array(
				'id'            => 'address',
				'name'          => __( 'Address', 'meta-box' ),
				'type'          => 'text',
				'std'           => __( 'Hanoi, Vietnam', 'meta-box' ),
			),
			array(
				'id'            => 'loc',
				'name'          => __( 'Location', 'meta-box' ),
				'type'          => 'map',
				'std'           => '-6.233406,-35.049906,15',     // 'latitude,longitude[,zoom]' (zoom is optional)
				'style'         => 'width: 500px; height: 500px',
				'address_field' => 'address',                     // Name of text field where address is entered. Can be list of text fields, separated by commas (for ex. city, state)
			),
		),
	);

	return $meta_boxes;
}
