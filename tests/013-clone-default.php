<?php
/**
 * This file tests the clone default value.
 *
 * @package Meta Box
 */

add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		$meta_boxes[] = [
			'title'  => 'Clone default values for simple fields',
			'fields' => [
				[
					'type'          => 'text',
					'name'          => 'Text',
					'id'            => 'text',
					'std'           => 'Default value',
					'clone'         => true,
					'clone_default' => true,
				],
				[
					'type'          => 'select',
					'name'          => 'Select',
					'id'            => 'select',
					'options'       => [
						'us' => 'USA',
						'fr' => 'France',
						'gb' => 'Great Britain',
					],
					'std'           => 'fr',
					'clone'         => true,
					'clone_default' => true,
				],
				[
					'type'          => 'radio',
					'name'          => 'Radio',
					'id'            => 'radio',
					'options'       => [
						'us' => 'USA',
						'fr' => 'France',
						'gb' => 'Great Britain',
					],
					'std'           => 'fr',
					'clone'         => true,
					'clone_default' => true,
				],
				[
					'type'          => 'checkbox',
					'name'          => 'Checkbox',
					'id'            => 'checkbox',
					'std'           => 1,
					'clone'         => true,
					'clone_default' => true,
				],
			],
		];

		$meta_boxes[] = [
			'title'  => 'Clone default values for groups',
			'fields' => [
				[
					'type'   => 'group',
					'id'     => 'group',
					'name'   => 'Group',
					'clone'  => true,
					'fields' => [
						[
							'type'          => 'text',
							'name'          => 'Text',
							'id'            => 'text2',
							'std'           => 'Default value',
							'clone_default' => true,
						],
						[
							'type'          => 'select',
							'name'          => 'Select',
							'id'            => 'select2',
							'options'       => [
								'us' => 'USA',
								'fr' => 'France',
								'gb' => 'Great Britain',
							],
							'std'           => 'fr',
							'clone_default' => true,
						],
						[
							'type'          => 'radio',
							'name'          => 'Radio',
							'id'            => 'radio2',
							'options'       => [
								'us' => 'USA',
								'fr' => 'France',
								'gb' => 'Great Britain',
							],
							'std'           => 'fr',
							'clone_default' => true,
						],
						[
							'type'          => 'checkbox',
							'name'          => 'Checkbox',
							'id'            => 'checkbox2',
							'std'           => 1,
							'clone_default' => true,
						],
					],
				],
			],
		];
		return $meta_boxes;
	}
);
