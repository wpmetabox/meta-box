<?php
// Steps to run this test:
// 1. composer install --dev
// 2. ./vendor/bin/phpunit

$base_dir = dirname( __DIR__ );
$plugins_dir = dirname( $base_dir );
$wp_dir = dirname( dirname( $plugins_dir ) );

// Load local WP
$wp_load_file = $wp_dir . '/wp-load.php';

print_r( $wp_load_file );

// Load WP on Scrutinizer-CI
if ( ! file_exists( $wp_load_file ) && ! empty( $_ENV['SCRUTINIZER'] ) ) {
	$wp_load_file = $base_dir . '/wordpress/wp-load.php';
}

if ( ! file_exists( $wp_load_file ) ) {
	return;
}

require_once $wp_load_file;
require_once $base_dir . '/vendor/autoload.php';
