<?php
add_action( 'admin_init', 'YOUR_PREFIX_register_meta_boxes' );

function YOUR_PREFIX_register_meta_boxes()
{
	if ( !class_exists( 'RW_Meta_Box' ) )
		return;

	$prefix = 'YOUR_PREFIX_';
	$meta_box = array(
		'title' => 'Date Time Picker With JS Options',

		'fields' => array(
			array(
				'name' => 'Date',
				'id'   => $prefix . 'date',
				'type' => 'date',

				// jQuery date picker options. See here http://jqueryui.com/demos/datepicker
				'js_options' => array(
					'appendText'      => '(yyyy-mm-dd)',
					'autoSize'        => true,
					'buttonText'      => 'Select Date',
					'dateFormat'      => 'yy-mm-dd',
					'numberOfMonths'  => 2,
					'showButtonPanel' => true,
				),
			),
			array(
				'name' => 'Datetime',
				'id'   => $prefix . 'datetime',
				'type' => 'datetime',

				// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
				'js_options' => array(
					'stepMinute'     => 15,
					'showTimepicker' => true,
				),
			),
			array(
				'name' => 'Time',
				'id'   => $prefix . 'time',
				'type' => 'time',

				// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
				'js_options' => array(
					'stepMinute' => 5,
					'showSecond' => true,
					'stepSecond' => 10,
				),
			),
		),
	);

	new RW_Meta_Box( $meta_box );
}