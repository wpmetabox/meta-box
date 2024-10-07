<?php
/**
 * This file test how fields are translated by WPML.
 *
 * @package Meta Box
 */

add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		$meta_boxes[] = [
			'title'  => 'WPML Test',
			'fields' => [
				[
					'name' => 'Translate this',
					'id'   => 'trans',
					'type' => 'text',
				],
				[
					'name' => 'Copy this',
					'id'   => 'copy',
					'type' => 'text',
				],
				[
					'name' => 'Do nothing with this',
					'id'   => 'nothing',
					'type' => 'text',
				],
				[
					'name' => 'Translate post ID',
					'id'   => 'post',
					'type' => 'post',
				],
				[
					'name' => 'Translate taxonomy advanced ID',
					'id'   => 'tax_adv',
					'type' => 'taxonomy_advanced',
				],
			],
		];

		return $meta_boxes;
	}
);
