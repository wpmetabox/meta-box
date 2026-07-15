<?php
use MetaBox\Integrations\BlockBindings;
use PHPUnit\Framework\TestCase;

class BlockBindingsTest extends TestCase {
	public function testSourceIsRegistered() {
		if ( ! get_block_bindings_source( BlockBindings::SOURCE_NAME ) ) {
			( new BlockBindings() )->register();
		}

		$source = get_block_bindings_source( BlockBindings::SOURCE_NAME );
		$this->assertNotNull( $source );
		$this->assertSame( BlockBindings::SOURCE_NAME, $source->name );
	}

	public function testUnwrapValueTakesFirstListItem() {
		$this->assertSame( 'first', BlockBindings::unwrap_value( [ 'first', 'second' ] ) );
		$this->assertSame( 42, BlockBindings::unwrap_value( [ [ 42, 99 ] ] ) );
		$this->assertNull( BlockBindings::unwrap_value( [] ) );
	}

	public function testUnwrapValueKeepsFileInfo() {
		$value = [
			'ID'  => 12,
			'url' => 'https://example.com/a.jpg',
			'alt' => 'A',
		];
		$this->assertSame( $value, BlockBindings::unwrap_value( $value ) );
	}

	public function testUnwrapValueTakesFirstFromIdKeyedMap() {
		$first = [ 'ID' => 12, 'url' => 'https://example.com/a.jpg' ];
		$map   = [
			12 => $first,
			34 => [ 'ID' => 34, 'url' => 'https://example.com/b.jpg' ],
		];
		$this->assertSame( $first, BlockBindings::unwrap_value( $map ) );
	}

	public function testFormatTextForContent() {
		$this->assertSame( 'Hello', BlockBindings::format_for_attribute( 'Hello', 'content' ) );
	}

	public function testFormatFileInfoAttributes() {
		$info = [
			'ID'       => 42,
			'url'      => 'https://example.com/thumb.jpg',
			'full_url' => 'https://example.com/full.jpg',
			'alt'      => 'Alt text',
			'title'    => 'Title',
			'caption'  => 'Caption',
		];

		$this->assertSame( 42.0, BlockBindings::format_for_attribute( $info, 'id' ) );
		$this->assertSame( 'https://example.com/full.jpg', BlockBindings::format_for_attribute( $info, 'url' ) );
		$this->assertSame( 'Alt text', BlockBindings::format_for_attribute( $info, 'alt' ) );
		$this->assertSame( 'Title', BlockBindings::format_for_attribute( $info, 'title' ) );
		$this->assertSame( 'Caption', BlockBindings::format_for_attribute( $info, 'caption' ) );
	}

	public function testFormatFileInfoFallsBackToUrl() {
		$info = [
			'ID'  => 42,
			'url' => 'https://example.com/photo.jpg',
		];

		$this->assertSame( 'https://example.com/photo.jpg', BlockBindings::format_for_attribute( $info, 'url' ) );
	}
}
