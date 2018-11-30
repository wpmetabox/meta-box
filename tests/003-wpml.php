<?php
/**
 * This file test how fields are translated by WPML.
 *
 * @package Meta Box
 */

add_filter(
	'rwmb_meta_boxes',
	function ( $meta_boxes ) {
		$meta_boxes[] = array(
			'title'  => 'WPML Test',
			'fields' => array(
				array(
					'name' => 'Translate this',
					'id'   => 'trans',
					'type' => 'text',
				),
				array(
					'name' => 'Copy this',
					'id'   => 'copy',
					'type' => 'text',
				),
				array(
					'name' => 'Do nothing with this',
					'id'   => 'nothing',
					'type' => 'text',
				),
				array(
					'name' => 'Translate post ID',
					'id'   => 'post',
					'type' => 'post',
				),
				array(
					'name' => 'Translate taxonomy advanced ID',
					'id'   => 'tax_adv',
					'type' => 'taxonomy_advanced',
				),
			),
		);

		return $meta_boxes;
	}
);
