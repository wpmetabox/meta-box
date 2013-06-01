<?php
add_action( 'admin_init', 'test_register_meta_boxes' );
function test_register_meta_boxes()
{
	if ( !class_exists( 'RW_Meta_Box' ) )
		return;

	$meta_box = array(
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

	new RW_Meta_Box( $meta_box );
}
