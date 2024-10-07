<?php
/**
 * Check: when cloning oembed field, the input must be cleared and the preview must be removed.
 */

add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		$meta_boxes[] = [
			'title'  => 'Test cloning oEmbed field',
			'fields' => [
				[
					'id'    => 'oembed',
					'name'  => 'oEmbed',
					'type'  => 'oembed',
					'clone' => true,
				],
				[
					'id'     => 'group',
					'name'   => 'Inside group',
					'type'   => 'group',
					'fields' => [
						[
							'id'    => 'oembed',
							'name'  => 'oEmbed',
							'type'  => 'oembed',
							'clone' => true,
						],
					],
				],
			],
		];

		return $meta_boxes;
	}
);
