<?php
add_filter( 'rwmb_meta_boxes', 'your_prefix_choice_demo' );
function your_prefix_choice_demo( $meta_boxes ) {
	$prefix       = '';
	$meta_boxes[] = [
		'title'  => __( 'Hierarchical Options', 'your-prefix' ),
		'fields' => [
			[
				'id'      => $prefix . 'checkbox_list',
				'name'    => __( 'Flat Checkbox', 'your-prefix' ),
				'type'    => 'checkbox_list',
				// Old options syntax
				'options' => [
					'option1' => 'Option 1',
					'option2' => 'Option 2',
					'option3' => 'Option 3',
				],
			],

			[
				'id'       => $prefix . 'checkbox_tree',
				'name'     => __( 'Hierarchical Checkbox', 'your-prefix' ),
				'type'     => 'checkbox_list',
				// New options syntax.  Supports hierarchical options with addition of parent
				'options'  => [
					[
						'value' => 'option1',
						'label' => 'Option 1',
					],
					[
						'value' => 'option2',
						'label' => 'Option 2',
					],
					[
						'value' => 'option3',
						'label' => 'Option 3',
					],
					[
						'value'  => 'suboption11',
						'label'  => 'SubOption 1',
						'parent' => 'option1',
					],
					[
						'value'  => 'suboption12',
						'label'  => 'SubOption 2',
						'parent' => 'option1',
					],
					[
						'value'  => 'suboption13',
						'label'  => 'SubOption 3',
						'parent' => 'option2',
					],
				],
				'collapse' => true,
				'flatten'  => false,
			],

			[
				'id'      => $prefix . 'select',
				'name'    => __( 'Hierarchical select', 'your-prefix' ),
				'type'    => 'select',
				'options' => [
					[
						'value' => 'option1',
						'label' => 'Option 1',
					],
					[
						'value' => 'option2',
						'label' => 'Option 2',
					],
					[
						'value' => 'option3',
						'label' => 'Option 3',
					],
					[
						'value'  => 'suboption11',
						'label'  => 'SubOption 1',
						'parent' => 'option1',
					],
					[
						'value'  => 'suboption12',
						'label'  => 'SubOption 2',
						'parent' => 'option1',
					],
					[
						'value'  => 'suboption13',
						'label'  => 'SubOption 3',
						'parent' => 'option2',
					],
				],
				'flatten' => false,
			],
		],
	];
	return $meta_boxes;
}
