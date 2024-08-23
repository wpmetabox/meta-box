<?php
/**
 * Plugin Name: Meta Box
 * Plugin URI:  https://metabox.io
 * Description: Create custom meta boxes and custom fields in WordPress.
 * Version:     5.10.0
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box
 * Domain Path: /languages/
 *
 * @package Meta Box
 */

if ( defined( 'ABSPATH' ) && ! defined( 'RWMB_VER' ) ) {
	require_once __DIR__ . '/inc/loader.php';
	$rwmb_loader = new RWMB_Loader();
	$rwmb_loader->init();
}

function product_links_get_meta_box( $meta_boxes ) {
	// return $meta_boxes;

	$prefix = 'product-links-';

	$meta_boxes[] = array(
		'id' => 'product_links',
		'title' => esc_html__( 'Product Links', 'textdomain' ),
		'post_types' => array( 'post' ),
		'priority' => 'high',
		'autosave' => false,
		'fields' => array(
			array(
				'id' => $prefix . 'stores',
				'type' => 'text_list',
				'name' => esc_html__( 'stores', 'textdomain' ),
				'desc' => esc_html__( 'Add external product links', 'textdomain' ),
				'clone' => true,
				'options' => array(
					'name' => 'Store',
					'url' => 'URL'
				),
				'add_button' => esc_html__( 'Add Store', 'textdomain' ),
			),
		),
	);

	return $meta_boxes;
}
add_filter( 'rwmb_meta_boxes', 'product_links_get_meta_box' );