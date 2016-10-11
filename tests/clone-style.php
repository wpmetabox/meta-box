<?php
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = array(
		'title'  => 'Test Clone Style',
		'fields' => array(
			array(
				'type' => 'heading',
				'name' => 'Text field, no name, no sort clone',
			),
			array(
				'id'    => 'text1',
				'type'  => 'text',
				'clone' => true,
			),
			array(
				'type' => 'heading',
				'name' => 'Text field, no name, sort clone',
			),
			array(
				'id'         => 'text2',
				'type'       => 'text',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'type' => 'heading',
				'name' => 'Text field, name, no sort clone',
			),
			array(
				'id'    => 'text3',
				'name'  => 'No sort clone',
				'type'  => 'text',
				'clone' => true,
			),
			array(
				'type' => 'heading',
				'name' => 'Text field, name, sort clone',
			),
			array(
				'id'         => 'text4',
				'name'       => 'Sort clone',
				'type'       => 'text',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'type' => 'heading',
				'name' => 'Select field',
			),
			array(
				'id'         => 'select1',
				'name'       => 'Select Single',
				'type'       => 'select',
				'clone'      => true,
				'sort_clone' => true,
				'options'    =>
					array(
						'm' => 'Male',
						'f' => 'Female',
					),
			),
			array(
				'id'         => 'select1a',
				'name'       => 'Select Single',
				'type'       => 'select',
				'clone'      => true,
				'sort_clone' => false,
				'options'    =>
					array(
						'm' => 'Male',
						'f' => 'Female',
					),
			),
			array(
				'id'         => 'select2',
				'name'       => 'Select Multiple',
				'type'       => 'select',
				'clone'      => true,
				'sort_clone' => true,
				'multiple'   => true,
				'options'    =>
					array(
						'usa' => 'United States',
						'uk'  => 'United Kingdom',
						'vn'  => 'Vietnam',
						'ger' => 'Germany',
						'fr'  => 'France',
					),
			),
			array(
				'id'         => 'select2a',
				'name'       => 'Select Multiple',
				'type'       => 'select',
				'clone'      => true,
				'sort_clone' => false,
				'multiple'   => true,
				'options'    =>
					array(
						'usa' => 'United States',
						'uk'  => 'United Kingdom',
						'vn'  => 'Vietnam',
						'ger' => 'Germany',
						'fr'  => 'France',
					),
			),
			array(
				'id'         => 'select_advanced1',
				'name'       => 'Select Advanced',
				'type'       => 'select_advanced',
				'multiple'   => true,
				'clone'      => true,
				'sort_clone' => true,
				'options'    =>
					array(
						'usa' => 'United States',
						'uk'  => 'United Kingdom',
						'vn'  => 'Vietnam',
						'ger' => 'Germany',
						'fr'  => 'France',
					),
			),
			array(
				'id'         => 'select_advanced1a',
				'name'       => 'Select Advanced',
				'type'       => 'select_advanced',
				'multiple'   => true,
				'clone'      => true,
				'sort_clone' => false,
				'options'    =>
					array(
						'usa' => 'United States',
						'uk'  => 'United Kingdom',
						'vn'  => 'Vietnam',
						'ger' => 'Germany',
						'fr'  => 'France',
					),
			),
			array(
				'type' => 'heading',
				'name' => 'Radio and checkbox field',
			),
			array(
				'id'         => 'radio1',
				'name'       => 'Radio',
				'type'       => 'radio',
				'clone'      => true,
				'sort_clone' => true,
				'options'    =>
					array(
						'm' => 'Male',
						'f' => 'Female',
					),
			),
			array(
				'id'         => 'checkbox1',
				'name'       => 'Checkbox',
				'type'       => 'checkbox',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'checkbox2',
				'name'       => 'Checkbox List',
				'type'       => 'checkbox_list',
				'clone'      => true,
				'sort_clone' => true,
				'options'    =>
					array(
						'usa' => 'United States',
						'uk'  => 'United Kingdom',
						'vn'  => 'Vietnam',
						'ger' => 'Germany',
						'fr'  => 'France',
					),
			),
			array(
				'type' => 'heading',
				'name' => 'Date time field',
			),
			array(
				'id'         => 'date1',
				'name'       => 'Date',
				'type'       => 'date',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'time1',
				'name'       => 'Time',
				'type'       => 'time',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'datetime1',
				'name'       => 'Datetime',
				'type'       => 'datetime',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'type' => 'heading',
				'name' => 'Other field',
			),
			array(
				'id'         => 'color1',
				'name'       => 'Color',
				'type'       => 'color',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'oembed',
				'name'       => 'Oembed',
				'type'       => 'oembed',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'slider1',
				'name'       => 'Slider',
				'type'       => 'slider',
				'clone'      => true,
				'sort_clone' => true,
			),
			array(
				'id'         => 'post1',
				'name'       => 'Post',
				'type'       => 'post',
				'clone'      => true,
				'sort_clone' => true,
			),
		),
	);
	return $meta_boxes;
} );
