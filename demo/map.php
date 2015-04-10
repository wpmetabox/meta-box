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
				'marker'      		 => true, 					  // Display marker? Default is 'true',
			    'marker_settings'	 => array(					  // Use your own marker for maps
			    	'url'	=> 'http://openclipart.org/image/800px/svg_to_png/168985/map-marker-23x31-active.png', // Give an image with the map
			    	'size'	=> '40,60'							  // Don't forget the size
			    	),
			),
		),
	);

	return $meta_boxes;
}
