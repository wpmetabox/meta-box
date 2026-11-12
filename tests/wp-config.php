<?php
// Settings for the WordPress test suite. Override any value with an environment variable.
// WordPress core: WP_CORE_DIR if set, else the WordPress install this plugin sits in (wp-content/plugins/<plugin>).
define( 'ABSPATH', getenv( 'WP_CORE_DIR' ) ? rtrim( getenv( 'WP_CORE_DIR' ), '/' ) . '/' : dirname( __DIR__, 4 ) . '/' );
if ( ! file_exists( ABSPATH . 'wp-settings.php' ) ) {
	echo 'WordPress not found at ' . ABSPATH . '. Set WP_CORE_DIR.' . PHP_EOL;
	exit( 1 );
}
// Use an empty wp-content so the site's mu-plugins, drop-ins and uploads are not touched.
define( 'WP_CONTENT_DIR', sys_get_temp_dir() . '/wp-tests-content' );
// Keep plugin paths (plugin_basename(), plugins_url(), get_plugin_data()) pointing at the real plugins folder.
define( 'WP_PLUGIN_DIR', dirname( __DIR__, 2 ) );
define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_test' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) !== false ? getenv( 'WP_TESTS_DB_PASSWORD' ) : 'root' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_DEBUG', true );

// Create the test database if it does not exist, so a new machine only needs a running MySQL.
( new mysqli( DB_HOST, DB_USER, DB_PASSWORD ) )->query( 'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '`' );
