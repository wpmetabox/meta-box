<?php
/*
Plugin Name: Meta Box
Plugin URI: http://www.deluxeblogtips.com/meta-box
Description: Create meta box for editing pages in WordPress. Compatible with custom post types since WP 3.0
Version: 4.1.4
Author: Rilwis
Author URI: http://www.deluxeblogtips.com
License: GPL2+
*/

// Prevent loading this file directly - Busted!
if ( ! class_exists( 'WP' ) )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// Optimize code for loading plugin files ONLY on admin side
// @see http://goo.gl/EhMFP
if ( defined( 'WP_ADMIN' ) && WP_ADMIN )
{
	// Script version, used to add version for scripts and styles
	define( 'RWMB_VER', '4.1.2' );

	// Define plugin URLs, for fast enqueuing scripts and styles
	if ( ! defined( 'RWMB_URL' ) )
		define( 'RWMB_URL', plugin_dir_url( __FILE__ ) );
	define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
	define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

	// Plugin paths, for including files
	if ( ! defined( 'RWMB_DIR' ) )
		define( 'RWMB_DIR', plugin_dir_path( __FILE__ ) );
	define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
	define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );
	define( 'RWMB_CLASSES_DIR', trailingslashit( RWMB_INC_DIR . 'classes' ) );

	// Include field classes
	foreach ( glob( RWMB_FIELDS_DIR . '*.php' ) as $file )
	{
		require_once $file;
	}

	// Include plugin main file
	require_once RWMB_CLASSES_DIR . 'meta-box.php';
}


/**
 * Adds [whatever] to the global debug array
 *
 * @param mixed  $input
 * @param string $print_or_export
 *
 * @return array
 */
function rwmb_debug( $input, $print_or_export = 'print' )
{
	global $rwmb_debug;

	$html = 'print' === $print_or_export ? print_r( $input, true ) : var_export( $input, true );

	return $rwmb_debug[] = $html;
}

/**
 * Prints or exports the content of the global debug array at the 'shutdown' hook
 *
 * @return void
 */
function rwmb_debug_print()
{
	global $rwmb_debug;
	if ( ! $rwmb_debug || ( is_user_logged_in() && is_user_admin() ) )
		return;

	$html  = '<h3>' . __( 'RW_Meta_Box Debug:', 'rwmb' ) . '</h3><pre>';
	foreach ( $rwmb_debug as $debug )
	{
		$html .= "{$debug}<hr />";
	}
	$html .= '</pre>';

	die( $html );
}

add_action( 'shutdown', 'rwmb_debug_print', 999 );
