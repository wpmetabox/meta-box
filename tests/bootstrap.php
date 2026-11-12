<?php
require dirname( __DIR__ ) . '/vendor/autoload.php';

$tests_dir = getenv( 'WP_PHPUNIT__DIR' );
require $tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function () {
	require dirname( __DIR__ ) . '/meta-box.php';
} );

require $tests_dir . '/includes/bootstrap.php';
