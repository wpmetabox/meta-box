<?php
add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		$meta_boxes[] = [
			'title'  => 'Test Clone Style',
			'fields' => [
				[
					'type' => 'heading',
					'name' => 'Text field, no name, no sort clone',
				],
				[
					'id'    => 'text1',
					'type'  => 'text',
					'clone' => true,
				],
				[
					'type' => 'heading',
					'name' => 'Text field, no name, sort clone',
				],
				[
					'id'         => 'text2',
					'type'       => 'text',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'type' => 'heading',
					'name' => 'Text field, name, no sort clone',
				],
				[
					'id'    => 'text3',
					'name'  => 'No sort clone',
					'type'  => 'text',
					'clone' => true,
				],
				[
					'type' => 'heading',
					'name' => 'Text field, name, sort clone',
				],
				[
					'id'         => 'text4',
					'name'       => 'Sort clone',
					'type'       => 'text',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'type' => 'heading',
					'name' => 'Select field',
				],
				[
					'id'         => 'select1',
					'name'       => 'Select Single',
					'type'       => 'select',
					'clone'      => true,
					'sort_clone' => true,
					'options'    =>
						[
							'm' => 'Male',
							'f' => 'Female',
						],
				],
				[
					'id'         => 'select1a',
					'name'       => 'Select Single',
					'type'       => 'select',
					'clone'      => true,
					'sort_clone' => false,
					'options'    =>
						[
							'm' => 'Male',
							'f' => 'Female',
						],
				],
				[
					'id'         => 'select2',
					'name'       => 'Select Multiple',
					'type'       => 'select',
					'clone'      => true,
					'sort_clone' => true,
					'multiple'   => true,
					'options'    =>
						[
							'usa' => 'United States',
							'uk'  => 'United Kingdom',
							'vn'  => 'Vietnam',
							'ger' => 'Germany',
							'fr'  => 'France',
						],
				],
				[
					'id'         => 'select2a',
					'name'       => 'Select Multiple',
					'type'       => 'select',
					'clone'      => true,
					'sort_clone' => false,
					'multiple'   => true,
					'options'    =>
						[
							'usa' => 'United States',
							'uk'  => 'United Kingdom',
							'vn'  => 'Vietnam',
							'ger' => 'Germany',
							'fr'  => 'France',
						],
				],
				[
					'id'         => 'select_advanced1',
					'name'       => 'Select Advanced',
					'type'       => 'select_advanced',
					'multiple'   => true,
					'clone'      => true,
					'sort_clone' => true,
					'options'    =>
						[
							'usa' => 'United States',
							'uk'  => 'United Kingdom',
							'vn'  => 'Vietnam',
							'ger' => 'Germany',
							'fr'  => 'France',
						],
				],
				[
					'id'         => 'select_advanced1a',
					'name'       => 'Select Advanced',
					'type'       => 'select_advanced',
					'multiple'   => true,
					'clone'      => true,
					'sort_clone' => false,
					'options'    =>
						[
							'usa' => 'United States',
							'uk'  => 'United Kingdom',
							'vn'  => 'Vietnam',
							'ger' => 'Germany',
							'fr'  => 'France',
						],
				],
				[
					'type' => 'heading',
					'name' => 'Radio and checkbox field',
				],
				[
					'id'         => 'radio1',
					'name'       => 'Radio',
					'type'       => 'radio',
					'clone'      => true,
					'sort_clone' => true,
					'options'    =>
						[
							'm' => 'Male',
							'f' => 'Female',
						],
				],
				[
					'id'         => 'checkbox1',
					'name'       => 'Checkbox',
					'type'       => 'checkbox',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'checkbox2',
					'name'       => 'Checkbox List',
					'type'       => 'checkbox_list',
					'clone'      => true,
					'sort_clone' => true,
					'options'    =>
						[
							'usa' => 'United States',
							'uk'  => 'United Kingdom',
							'vn'  => 'Vietnam',
							'ger' => 'Germany',
							'fr'  => 'France',
						],
				],
				[
					'type' => 'heading',
					'name' => 'Date time field',
				],
				[
					'id'         => 'date1',
					'name'       => 'Date',
					'type'       => 'date',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'time1',
					'name'       => 'Time',
					'type'       => 'time',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'datetime1',
					'name'       => 'Datetime',
					'type'       => 'datetime',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'type' => 'heading',
					'name' => 'Other field',
				],
				[
					'id'         => 'color1',
					'name'       => 'Color',
					'type'       => 'color',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'oembed',
					'name'       => 'Oembed',
					'type'       => 'oembed',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'slider1',
					'name'       => 'Slider',
					'type'       => 'slider',
					'clone'      => true,
					'sort_clone' => true,
				],
				[
					'id'         => 'post1',
					'name'       => 'Post',
					'type'       => 'post',
					'clone'      => true,
					'sort_clone' => true,
				],
			],
		];
		return $meta_boxes;
	}
);
