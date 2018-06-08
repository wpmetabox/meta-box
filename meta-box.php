<?php
/**
 * Plugin Name: Meta Box
 * Plugin URI: https://metabox.io
 * Description: Create custom meta boxes and custom fields in WordPress.
 * Version: 4.14.11
 * Author: MetaBox.io
 * Author URI: https://metabox.io
 * License: GPL2+
 * Text Domain: meta-box
 * Domain Path: /languages/
 *
 * @package Meta Box
 */

if ( defined( 'ABSPATH' ) && ! defined( 'RWMB_VER' ) ) {
	require_once dirname( __FILE__ ) . '/inc/loader.php';
	$loader = new RWMB_Loader();
	$loader->init();
}
