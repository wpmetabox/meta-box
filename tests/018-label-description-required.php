<?php
/**
 * Test for position of the asterisk when the field is required and has label description.
 * https://metabox.io/support/topic/field-validation-required-appears-after-label-description/
 */
add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$meta_boxes[] = [
		'title' => 'Field required with label description',
		'fields' => [
			[
				'id'                => 'text_1',
				'type'              => 'text',
				'name'              => 'Text Field',
				'label_description' => 'This is a description',
			],
		],
		'validation' => [
			'rules' => [
				'text_1' => [
					'required' => true,
				],
			],
		],
	];
	return $meta_boxes;
} );