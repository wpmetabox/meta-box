<?php
// Steps to run this test:
// 1. composer install --dev
// 2. ./vendor/bin/phpunit

// Bootstrap WordPress for phpunit
require_once '../../../wp-load.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';


$meta_boxes = [
    'id' => 'test-register-meta',
    'title' => 'Test Register Meta',
    'revision' => true,
    'fields' => [
        // RegisterMetaTest::testFieldNotRegisteredByDefault()
        [
            'name' => 'rmt_simple_text',
            'desc' => 'Basic text field',
            'id'   => 'rmt_simple_text',
            'type' => 'text',
            'std' => 'Simple Text Default',
        ],
        // RegisterMetaTest::testRegisteredField()
        [
            'name' => 'rmt_text_register_meta',
            'desc' => 'Text No Register Meta Description',
            'id'   => 'rmt_text_register_meta',
            'type' => 'text',
            'register_meta' => true
        ],
        // RegisterMetaTest::testOverrideArgs()
        [
            'id' => 'rmt_text_override',
            'type' => 'text',
            'name' => 'rmt_text_override',
            'register_meta' => [
                'description' => 'This is an overridden description',
                'revisions_enabled' => false, // Should override value from meta box
                'single' => true,
                'show_in_rest' => false,
                'default' => 'This is a default value',
                'type' => 'string',
            ],
        ],
    ],
];

$mb = new RW_Meta_Box($meta_boxes);
$mb->register_fields();