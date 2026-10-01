<?php
// Steps to run this test:
// 1. composer install --dev
// 2. ./vendor/bin/phpunit

$base_dir    = dirname( __DIR__ );
$plugins_dir = dirname( $base_dir );
$wp_dir      = dirname( dirname( $plugins_dir ) );

// Load local WP
$wp_load_file = $wp_dir . '/wp-load.php';

// Load WP on Scrutinizer-CI
if ( file_exists( $base_dir . '/wordpress' ) ) {
	echo "Scrutinizer is on \n";
	$wp_load_file = $base_dir . '/wordpress/wp-load.php';
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $wp_load_file . "\n";

if ( file_exists( $wp_load_file ) ) {
	require_once $wp_load_file;
	require_once $base_dir . '/vendor/autoload.php';
	return;
}

// No WordPress install: stub WP APIs and boot Meta Box enough for unit tests.
require_once __DIR__ . '/phpunit/stubs/wordpress.php';
require_once $base_dir . '/vendor/autoload.php';

if ( ! defined( 'RWMB_VER' ) ) {
	define( 'RWMB_VER', '0.0.0-test' );
}
if ( ! defined( 'RWMB_DIR' ) ) {
	define( 'RWMB_DIR', trailingslashit( $base_dir ) );
}
if ( ! defined( 'RWMB_INC_DIR' ) ) {
	define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
}
if ( ! defined( 'RWMB_JS_DIR' ) ) {
	define( 'RWMB_JS_DIR', trailingslashit( RWMB_DIR . 'js' ) );
}
if ( ! defined( 'RWMB_CSS_DIR' ) ) {
	define( 'RWMB_CSS_DIR', trailingslashit( RWMB_DIR . 'css' ) );
}
if ( ! defined( 'RWMB_URL' ) ) {
	define( 'RWMB_URL', 'http://example.com/wp-content/plugins/meta-box/' );
}
if ( ! defined( 'RWMB_JS_URL' ) ) {
	define( 'RWMB_JS_URL', RWMB_URL . 'js/' );
}
if ( ! defined( 'RWMB_CSS_URL' ) ) {
	define( 'RWMB_CSS_URL', RWMB_URL . 'css/' );
}

require_once RWMB_INC_DIR . 'autoloader.php';
$autoloader = new RWMB_Autoloader();
$autoloader->add( RWMB_INC_DIR, 'RW_' );
$autoloader->add( RWMB_INC_DIR, 'RWMB_' );
$autoloader->add( RWMB_INC_DIR . 'about', 'RWMB_' );
$autoloader->add( RWMB_INC_DIR . 'fields', 'RWMB_', '_Field' );
$autoloader->add( RWMB_INC_DIR . 'walkers', 'RWMB_Walker_' );
$autoloader->add( RWMB_INC_DIR . 'interfaces', 'RWMB_', '_Interface' );
$autoloader->add( RWMB_INC_DIR . 'storages', 'RWMB_', '_Storage' );
$autoloader->add( RWMB_INC_DIR . 'helpers', 'RWMB_Helpers_' );
$autoloader->register();
