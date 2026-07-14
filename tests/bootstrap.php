<?php
// Steps to run this test:
// 1. composer install --dev
// 2. ./vendor/bin/phpunit

$wp_load = null;
$dir     = __DIR__;
for ( $i = 0; $i < 6; $i++ ) {
	$dir = dirname( $dir );
	if ( file_exists( $dir . '/wp-load.php' ) ) {
		$wp_load = $dir . '/wp-load.php';
		break;
	}
}

if ( ! $wp_load ) {
	fwrite( STDERR, "Could not find wp-load.php. Run PHPUnit from a WordPress install.\n" );
	exit( 1 );
}

require_once $wp_load;
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load Meta Box even when the plugin is not activated in this WordPress install.
if ( ! class_exists( 'RW_Meta_Box', false ) ) {
	require_once dirname( __DIR__ ) . '/meta-box.php';
}

$meta_boxes = [
	'id'       => 'test-register-meta',
	'title'    => 'Test Register Meta',
	'revision' => true,
	'fields'   => [
		// RegisterMetaTest::testFieldNotRegisteredByDefault()
		[
			'name' => 'rmt_simple_text',
			'desc' => 'Basic text field',
			'id'   => 'rmt_simple_text',
			'type' => 'text',
			'std'  => 'Simple Text Default',
		],
		// RegisterMetaTest::testRegisteredField()
		[
			'name'          => 'Registered Text',
			'desc'          => 'Text Register Meta Description',
			'id'            => 'rmt_text_register_meta',
			'type'          => 'text',
			'std'           => 'Default text',
			'register_meta' => true,
		],
		// RegisterMetaTest::testArrayRegisterMetaIsIgnored()
		[
			'id'            => 'rmt_text_array_ignored',
			'type'          => 'text',
			'name'          => 'Array Register Meta',
			'register_meta' => [
				'description' => 'Should not register',
			],
		],
	],
];

$mb = new RW_Meta_Box( $meta_boxes );
$mb->register_fields();
