<?php
add_filter( 'rwmb_meta_boxes', 'prefix_register_meta_box_slider' );
function prefix_register_meta_box_slider( $meta_boxes )
{
	$meta_boxes[] = array(
		'title' => __( 'Slider Demo', 'meta-box', 'meta-box' ),
		'fields' => array(
			array(
				'name' => __( 'Slider', 'meta-box' ),
				'id'   => 'slider',
				'type' => 'slider',

				// Text labels displayed before and after value
				'prefix' => __( '$', 'meta-box' ),
				'suffix' => __( ' USD', 'meta-box' ),

				// jQuery UI slider options. See here http://api.jqueryui.com/slider/
				'js_options' => array(
					'min'   => 10,
					'max'   => 255,
					'step'  => 5,
				),

				'clone' => true,
			),
		),
	);
	return $meta_boxes;
}