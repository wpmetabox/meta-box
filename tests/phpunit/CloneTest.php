<?php
use PHPUnit\Framework\TestCase;

class CloneTest extends TestCase {
	/**
	 * Mock the post meta data
	 * Note that we use 'key' => ['value'] to simulate the actual get_metadata() function return
	 *
	 * @var array
	 */
	protected $metadata = [
		// Simple text field, no clone
		'text'                  => [ 'text1' ],
		'text_clone'            => [
			[
				'saved1',
				'saved2',
			],
		],

		'select'                => [ 'a' ],
		'select_clone'          => [
			[
				'a',
				'b',
			],
		],

		'select_multiple'       => [ [ 'b', 'c' ] ],
		'select_multiple_clone' => [
			[
				[ 'a', 'b' ],
				[ 'b', 'c' ],
			],
		],
	];

	protected function setUp(): void {
		// Filter the post meta data so get_post_meta() returns our mock data
		add_filter('get_post_metadata', function ( $value, $object_id, $meta_key, $single ) {
			// We simulate is_saved check by checking if $object_id is odd or even
			if ( $object_id % 2 === 0 ) {
				return $value;
			}

			return $this->metadata[ $meta_key ] ?? $value;
		}, 10, 4);
	}

	protected function getMeta( array $field ) {
		return RWMB_Field::call( 'raw_meta', 1, $field );
	}

	/**
	 * Test with not clone field
	 *
	 * @param mixed $field
	 * @return void
	 */
	protected function withNotCloneTest( $field ) {
		if ( $field['clone'] ) {
			return;
		}

		// 1.1 Not Saved, should return the std value, default is empty string
		$meta = RWMB_Field::meta( 2, false, $field );
		$this->assertEquals( RWMB_Field::call( 'get_single_std', $field ), $meta );

		// 1.2 Set default value to $field['_optional_default'], should return $field['_optional_default']
		$field['std'] = $field['_optional_default'];
		$meta         = RWMB_Field::meta( 2, false, $field );
		$this->assertEquals( RWMB_Field::call( 'get_single_std', $field ), $meta );

		// 1.3 When saved, it should return the saved value
		$meta = RWMB_Field::meta( 1, true, $field );

		$this->assertEquals( $this->getMeta( $field ), $meta );
	}

	protected function withCloneTest( $field ) {
		if ( ! $field['clone'] ) {
			return;
		}

		$field['clone'] = true;
		$field['id']    = $field['id'] . '_clone';

		// Need to normalize the field again to apply _original_id
		$field = RWMB_Field::call( 'normalize', $field );
		// 1.0 No default value, should return Array<template, ''>
		$meta = RWMB_Field::meta( 2, false, $field );
		$this->assertGreaterThanOrEqual( 2, $meta );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );
		$this->assertEquals( $meta[1], RWMB_Field::call( 'get_single_std', $field ) );

		// 1.1 Not Saved, should return Array<template, std>
		$field['std'] = $field['_optional_default'];
		$meta         = RWMB_Field::meta( 2, false, $field );

		$this->assertGreaterThanOrEqual( 2, $meta );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );
		$this->assertEquals( $meta[1], RWMB_Field::call( 'get_single_std', $field ) );

		// 1.2 When saved, it should return Array<template, ...saved>
		$meta = RWMB_Field::meta( 1, true, $field );
		$this->assertGreaterThanOrEqual( 2, count( $meta ) );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );

		for ( $i = 1; $i < count( $meta ); $i++ ) {
			$this->assertEquals( $meta[ $i ], $this->getMeta( $field )[ $i - 1 ] );
		}
	}

	protected function withCloneEmptyStartTest( $field ) {
		if ( ! $field['clone'] || ! $field['clone_empty_start'] ) {
			return;
		}

		$field['id']                = $field['id'] . '_clone';
		$field['clone']             = true;
		$field['clone_empty_start'] = true;
		// Need to normalize the field again to apply _original_id
		$field = RWMB_Field::call( 'normalize', $field );

		// No default value, should return Array<template>
		$meta = RWMB_Field::meta( 2, false, $field );
		$this->assertCount( 1, $meta );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );

		$field['std'] = $field['_optional_default'];
		// Set default value to $field['_optional_default'], should return Array<template>
		// and template value should be $field['_optional_default']
		$meta = RWMB_Field::meta( 2, false, $field );
		$this->assertCount( 1, $meta );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );

		// When saved, it should return Array<template, saved1, saved2>
		$meta = RWMB_Field::meta( 1, true, $field );

		$this->assertCount( 3, $meta );
		$this->assertEquals( $meta[0], RWMB_Field::call( 'get_single_std', $field ) );

		for ( $i = 1; $i < count( $meta ); $i++ ) {
			$this->assertEquals( $meta[ $i ], $this->getMeta( $field )[ $i - 1 ] );
		}
	}

	public function runTestField( $field ) {
		$field = RWMB_Field::call( 'normalize', $field );
		$this->withNotCloneTest( $field );
		$this->withCloneTest( $field );
		$this->withCloneEmptyStartTest( $field );
	}

	public function testTextField() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$field = [
			'id'                => 'text',
			'type'              => 'text',
			'_optional_default' => 'default',
		];

		$this->runTestField( $field );

		$field = [
			...$field,
			'clone'             => true,
			'tm'                => 1,
			'_optional_default' => [ 'default1', 'default2' ],
		];
		RWMB_Field::call( 'normalize', $field );

		$this->runTestField( $field );
	}

	public function testSelectField() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$field = [
			'id'                => 'select',
			'type'              => 'select',
			'options'           => [
				'a' => 'A',
				'b' => 'B',
			],
			'_optional_default' => 'a',
		];

		$this->runTestField( $field );
	}

	public function testSelectMultipleField() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$field = [
			'id'                => 'select_multiple',
			'type'              => 'select',
			'options'           => [
				'a' => 'A',
				'b' => 'B',
				'c' => 'C',
			],
			'multiple'          => true,
			'_optional_default' => [ 'a', 'b' ],
		];

		$this->runTestField( $field );
	}
}
