<?php
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase {
	public function testNormalize() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$field = RWMB_Link_Field::normalize( [
			'type' => 'link',
			'id'   => 'my_link',
			'name' => 'My Link',
		] );

		$this->assertFalse( $field['multiple'] );
		$this->assertEquals( 'link', $field['type'] );
	}

	public function testValueSanitizes() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$new = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '_blank',
			'post_id' => '42',
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( 'https://example.com/test', $result['url'] );
		$this->assertEquals( 'Test Link', $result['title'] );
		$this->assertEquals( '_blank', $result['target'] );
		$this->assertEquals( 42, $result['post_id'] );
	}

	public function testValueReturnsEmptyForEmptyInputs() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$new = [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( [], $result );
	}

	public function testValueRejectsInvalidTarget() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$new = [
			'url'     => 'https://example.com',
			'title'   => 'Link',
			'target'  => 'invalid',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::value( $new, [], 1, [] );

		$this->assertEquals( '', $result['target'] );
	}

	public function testFormatSingleValue() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$value = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '_blank',
			'post_id' => 42,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '<a href="https://example.com/test" target="_blank">Test Link</a>', $result );
	}

	public function testFormatSingleValueNoTarget() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$value = [
			'url'     => 'https://example.com/test',
			'title'   => 'Test Link',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '<a href="https://example.com/test">Test Link</a>', $result );
	}

	public function testFormatSingleValueEmptyUrl() {
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}

		$value = [
			'url'     => '',
			'title'   => '',
			'target'  => '',
			'post_id' => 0,
		];

		$result = RWMB_Link_Field::format_single_value( [], $value, [], null );

		$this->assertEquals( '', $result );
	}
}
