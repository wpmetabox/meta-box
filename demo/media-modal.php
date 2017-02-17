<?php
/**
 * This demo shows you how to add custom fields to media modal when viewing/editing an attachment.
 *
 * @package Meta Box
 */

add_filter( 'rwmb_meta_boxes', function ( $meta_boxes ) {
	$prefix       = 'your_prefix_';
	$meta_boxes[] = array(
		'title'       => esc_html__( 'Standard Fields', 'your-prefix' ),

		'post_types'  => 'attachment', // Must set to 'attachment' or contains 'attachment'.
		'media_modal' => true,         // Must set to true.

		'fields'      => array(
			array(
				'name' => esc_html__( 'Text', 'your-prefix' ),
				'id'   => "{$prefix}text",
				'type' => 'text',
			),
			array(
				'name' => esc_html__( 'Checkbox', 'your-prefix' ),
				'id'   => "{$prefix}checkbox",
				'type' => 'checkbox',
			),
			array(
				'name'    => esc_html__( 'Radio', 'your-prefix' ),
				'id'      => "{$prefix}radio",
				'type'    => 'radio',
				'options' => array(
					'value1' => esc_html__( 'Label1', 'your-prefix' ),
					'value2' => esc_html__( 'Label2', 'your-prefix' ),
				),
			),
			array(
				'name'    => esc_html__( 'Select', 'your-prefix' ),
				'id'      => "{$prefix}select",
				'type'    => 'select',
				'options' => array(
					'value1' => esc_html__( 'Label1', 'your-prefix' ),
					'value2' => esc_html__( 'Label2', 'your-prefix' ),
				),
			),
			array(
				'name' => esc_html__( 'Textarea', 'your-prefix' ),
				'id'   => "{$prefix}textarea",
				'type' => 'textarea',
			),
		),
	);

	return $meta_boxes;
} );
