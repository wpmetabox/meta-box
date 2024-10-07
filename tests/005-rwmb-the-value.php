<?php
/**
 * This file test the helper function rwmb_the_value with the optimization in "helper" branch since 4.8.7
 */
global $test;
add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		global $test;
		$prefix = '';
		$test   = [
			'title'  => __( 'Test helper functions', 'your-prefix' ),
			'fields' => [
				[
					'id'   => 'checkbox',
					'name' => __( 'Checkbox', 'your-prefix' ),
					'type' => 'checkbox',
				],
				[
					'id'   => 'color',
					'name' => __( 'Color', 'your-prefix' ),
					'type' => 'color',
				],
				[
					'id'    => 'color2',
					'name'  => __( 'Color clone', 'your-prefix' ),
					'type'  => 'color',
					'clone' => true,
				],

				// Object choice
				[
					'name'       => __( 'Post', 'your-prefix' ),
					'id'         => 'post',
					'type'       => 'post',
					'post_type'  => 'post',
					'field_type' => 'select_advanced',
				],
				[
					'name'       => __( 'Post Multiple', 'your-prefix' ),
					'id'         => 'post2',
					'type'       => 'post',
					'multiple'   => true,
					'post_type'  => 'post',
					'field_type' => 'select_advanced',
				],
				[
					'name'       => __( 'Post Clone', 'your-prefix' ),
					'id'         => 'post3',
					'type'       => 'post',
					'clone'      => true,
					'post_type'  => 'post',
					'field_type' => 'select_advanced',
				],
				[
					'name'       => __( 'Post Clone and Multiple', 'your-prefix' ),
					'id'         => 'post4',
					'type'       => 'post',
					'clone'      => true,
					'multiple'   => true,
					'post_type'  => 'post',
					'field_type' => 'select_advanced',
				],

				// Checkbox list
				[
					'name'    => esc_html__( 'Checkbox list', 'your-prefix' ),
					'id'      => "{$prefix}checkbox_list",
					'type'    => 'checkbox_list',
					'options' => [
						'value1' => esc_html__( 'Label1', 'your-prefix' ),
						'value2' => esc_html__( 'Label2', 'your-prefix' ),
					],
				],
				[
					'name'    => esc_html__( 'Checkbox list clone', 'your-prefix' ),
					'id'      => "{$prefix}checkbox_list2",
					'type'    => 'checkbox_list',
					'clone'   => true,
					'options' => [
						'value1' => esc_html__( 'Label1', 'your-prefix' ),
						'value2' => esc_html__( 'Label2', 'your-prefix' ),
					],
				],

				// Radio
				[
					'name'    => __( 'Radio', 'your-prefix' ),
					'id'      => 'radio',
					'type'    => 'radio',
					'options' => [
						'value1' => __( 'Label1', 'your-prefix' ),
						'value2' => __( 'Label2', 'your-prefix' ),
					],
				],
				[
					'name'    => __( 'Radio clone', 'your-prefix' ),
					'id'      => 'radio2',
					'type'    => 'radio',
					'clone'   => true,
					'options' => [
						'value1' => __( 'Label1', 'your-prefix' ),
						'value2' => __( 'Label2', 'your-prefix' ),
					],
				],

				// oembed
				[
					'id'   => 'oembed',
					'name' => __( 'oEmbed', 'your-prefix' ),
					'type' => 'oembed',
				],
				[
					'id'    => 'oembed2',
					'name'  => __( 'oEmbed clone', 'your-prefix' ),
					'type'  => 'oembed',
					'clone' => true,
				],

				// Image select
				[
					'id'      => 'image_select',
					'name'    => __( 'Image Select', 'your-prefix' ),
					'type'    => 'image_select',
					'options' => [
						'left'  => 'http://placehold.it/90x90&text=Left',
						'right' => 'http://placehold.it/90x90&text=Right',
						'none'  => 'http://placehold.it/90x90&text=None',
					],
				],
				[
					'id'       => 'image_select2',
					'name'     => __( 'Image Select Multiple', 'your-prefix' ),
					'type'     => 'image_select',
					'options'  => [
						'left'  => 'http://placehold.it/90x90&text=Left',
						'right' => 'http://placehold.it/90x90&text=Right',
						'none'  => 'http://placehold.it/90x90&text=None',
					],
					'multiple' => true,
				],
				[
					'id'      => 'image_select3',
					'name'    => __( 'Image Select Clone', 'your-prefix' ),
					'type'    => 'image_select',
					'options' => [
						'left'  => 'http://placehold.it/90x90&text=Left',
						'right' => 'http://placehold.it/90x90&text=Right',
						'none'  => 'http://placehold.it/90x90&text=None',
					],
					'clone'   => true,
				],

				// Autocomplete
				[
					'name'    => esc_html__( 'Autocomplete', 'your-prefix' ),
					'id'      => "{$prefix}autocomplete",
					'type'    => 'autocomplete',
					'options' => [
						'value1' => esc_html__( 'Label1', 'your-prefix' ),
						'value2' => esc_html__( 'Label2', 'your-prefix' ),
					],
				],

				// Key-value
				[
					'id'   => 'key_value',
					'name' => __( 'Key Value', 'your-prefix' ),
					'type' => 'key_value',
					'desc' => __( 'Add more additional info below:', 'your-prefix' ),
				],

				// Fieldset text
				[
					'id'      => 'fieldset_text',
					'name'    => __( 'Fieldset Text', 'your-prefix' ),
					'type'    => 'fieldset_text',
					'desc'    => __( 'Please enter following details:', 'your-prefix' ),
					'options' => [
						'name'    => __( 'Name', 'your-prefix' ),
						'address' => __( 'Address', 'your-prefix' ),
						'email'   => __( 'Email', 'your-prefix' ),
					],
				],
				[
					'id'      => 'fieldset_text2',
					'name'    => __( 'Fieldset Text Clone', 'your-prefix' ),
					'type'    => 'fieldset_text',
					'desc'    => __( 'Please enter following details:', 'your-prefix' ),
					'options' => [
						'name'    => __( 'Name', 'your-prefix' ),
						'address' => __( 'Address', 'your-prefix' ),
						'email'   => __( 'Email', 'your-prefix' ),
					],
					'clone'   => true,
				],

				// Text list
				[
					'id'      => 'text_list',
					'name'    => __( 'Text List', 'your-prefix' ),
					'type'    => 'text_list',
					'options' => [
						'John Smith'      => __( 'Name', 'your-prefix' ),
						'name@domain.com' => __( 'Email', 'your-prefix' ),
					],
				],
				[
					'id'      => 'text_list2',
					'name'    => __( 'Text List Clone', 'your-prefix' ),
					'type'    => 'text_list',
					'options' => [
						'John Smith'      => __( 'Name', 'your-prefix' ),
						'name@domain.com' => __( 'Email', 'your-prefix' ),
					],
					'clone'   => true,
				],

				// File
				[
					'id'   => 'file',
					'name' => __( 'File', 'your-prefix' ),
					'type' => 'file',
				],
				[
					'id'   => 'fileadv',
					'name' => __( 'File Advanced', 'your-prefix' ),
					'type' => 'file_advanced',
				],
				[
					'id'    => 'fileadv2',
					'name'  => __( 'File Advanced Clone', 'your-prefix' ),
					'type'  => 'file_advanced',
					'clone' => true,
				],

				// Image
				[
					'id'   => 'image',
					'name' => __( 'Image', 'your-prefix' ),
					'type' => 'image',
				],
				[
					'id'   => 'image_advanced',
					'name' => __( 'Image Advanced', 'your-prefix' ),
					'type' => 'image_advanced',
				],
				[
					'id'    => 'image_advanced2',
					'name'  => __( 'Image Advanced Clone', 'your-prefix' ),
					'type'  => 'image_advanced',
					'clone' => true,
				],
			],
		];
		$meta_boxes[] = $test;
		return $meta_boxes;
	}
);

add_filter(
	'the_content',
	function ( $content ) {
		global $test;
		$output = '';
		foreach ( $test['fields'] as $field ) {
			$output .= '<p><strong>Field ' . $field['name'] . ':</strong> ' . rwmb_the_value( $field['id'], '', null, false ) . '</p>';
		}
		return $output . $content;
	}
);
