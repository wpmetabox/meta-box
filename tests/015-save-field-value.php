<?php
/**
 * This script tests the value of field is saved correctly when combine the settings 'clone', 'clone_as_multiple' and 'multiple'.
 * It targets to change in version 4.15.8
 * Date: Nov 30, 2018
 */
add_filter( 'rwmb_meta_boxes', function( $meta_boxes ) {
	$meta_boxes[] = [
		'title' => 'Test save field value',
		'fields' => [
			[
				'type'              => 'select',
				'id'                => 's1',
				'name'              => 'S1: Clone + Clone As Multiple + Multiple',
				'clone'             => true,
				'clone_as_multiple' => true,
				'multiple'          => true,
				'options'           => [
					'r' => 'Red',
					'b' => 'Blue',
				],
			],
			[
				'type'              => 'text',
				'id'                => 't1',
				'name'              => 'T1: Clone + Clone As Multiple',
				'clone'             => true,
				'clone_as_multiple' => true,
			],
			[
				'type'     => 'select',
				'id'       => 's2',
				'name'     => 'S2: Clone + Multiple',
				'clone'    => true,
				'multiple' => true,
				'options'  => [
					'r' => 'Red',
					'b' => 'Blue',
				],
			],
			[
				'type'  => 'text',
				'id'    => 't2',
				'name'  => 'T2: Clone',
				'clone' => true,
			],
			[
				'type'              => 'select',
				'id'                => 's3',
				'name'              => 'S3: Clone As Multiple + Multiple',
				'clone_as_multiple' => true,
				'multiple'          => true,
				'options'           => [
					'r' => 'Red',
					'b' => 'Blue',
				],
			],
			[
				'type'              => 'text',
				'id'                => 't3',
				'name'              => 'T3: Clone As Multiple',
				'clone_as_multiple' => true,
			],
			[
				'type'     => 'select',
				'id'       => 's4',
				'name'     => 'S4: Multiple',
				'multiple' => true,
				'options'  => [
					'r' => 'Red',
					'b' => 'Blue',
				],
			],
			[
				'type' => 'text',
				'id'   => 't4',
				'name' => 'T4',
			],
		],
	];
	$meta_boxes[] = [
		'title' => 'Special cases',
		'fields' => [
			[
				'type'              => 'image_advanced',
				'id'                => 'ia1',
				'name'              => 'IA1: Clone + Clone As Multiple + Multiple',
				'clone'             => true,
				'clone_as_multiple' => true,
			],
			[
				'type'  => 'image_advanced',
				'id'    => 'ia2',
				'name'  => 'IA2: Clone + Multiple',
				'clone' => true,
			],
		],
	];
	return $meta_boxes;
} );
