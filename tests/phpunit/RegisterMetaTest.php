<?php
use PHPUnit\Framework\TestCase;

class RegisterMetaTest extends TestCase {
	private $meta;

	public function setUp(): void {
		$this->meta = get_registered_meta_keys( 'post', 'post' );
	}

	public function testFieldNotRegisteredByDefault() {
		$this->assertArrayNotHasKey( 'rmt_simple_text', $this->meta );
	}

	public function testRegisteredField() {
		$this->assertArrayHasKey( 'rmt_text_register_meta', $this->meta );
	}

	public function testArrayRegisterMetaIsIgnored() {
		$this->assertArrayNotHasKey( 'rmt_text_array_ignored', $this->meta );
	}

	public function testRegisteredArgs() {
		$args = $this->meta['rmt_text_register_meta'];

		$this->assertSame( 'string', $args['type'] );
		$this->assertSame( 'Registered Text', $args['label'] );
		$this->assertSame( 'Text Register Meta Description', $args['description'] );
		$this->assertSame( 'Default text', $args['default'] );
		$this->assertTrue( $args['single'] );
		$this->assertTrue( $args['show_in_rest'] );
		$this->assertTrue( $args['revisions_enabled'] );
	}
}
