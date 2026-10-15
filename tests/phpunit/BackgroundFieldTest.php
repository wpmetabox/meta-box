<?php
use PHPUnit\Framework\TestCase;

class BackgroundFieldTest extends TestCase {
	private RWMB_Sanitizer $sanitizer;

	protected function setUp(): void {
		parent::setUp();
		if ( ! defined( 'RWMB_VER' ) ) {
			$this->markTestSkipped( 'Meta Box is not active' );
		}
		$this->sanitizer = new RWMB_Sanitizer();
	}

	public function testSanitizeDiscardsUnexpectedKeys(): void {
		$result = $this->sanitizer->sanitize(
			[
				'color'                          => '#ffffff',
				'image'                          => 'https://example.com/bg.jpg',
				'repeat'                         => 'no-repeat',
				'attachment'                     => 'scroll',
				'position'                       => 'center center',
				'size'                           => 'cover',
				'x"><img src=x onerror=alert(1)>' => '1',
			],
			[
				'type'              => 'background',
				'sanitize_callback' => null,
			]
		);

		$this->assertSame(
			[
				'color'      => '#ffffff',
				'image'      => 'https://example.com/bg.jpg',
				'repeat'     => 'no-repeat',
				'position'   => 'center center',
				'attachment' => 'scroll',
				'size'       => 'cover',
			],
			$result
		);
	}

	public function testSanitizeRejectsNonStringProperties(): void {
		$result = $this->sanitizer->sanitize(
			[
				'color'      => [ 'evil' ],
				'image'      => [ 'https://example.com/bg.jpg' ],
				'repeat'     => [ 'repeat' ],
				'attachment' => 'scroll',
				'position'   => 'top left',
				'size'       => 'cover',
			],
			[
				'type'              => 'background',
				'sanitize_callback' => null,
			]
		);

		$this->assertSame( '', $result['color'] );
		$this->assertSame( '', $result['image'] );
		$this->assertSame( '', $result['repeat'] );
		$this->assertSame( 'scroll', $result['attachment'] );
		$this->assertSame( 'top left', $result['position'] );
		$this->assertSame( 'cover', $result['size'] );
	}

	public function testFormatIgnoresUnexpectedKeys(): void {
		$result = RWMB_Background_Field::format_single_value(
			[],
			[
				'color'                          => '#fff',
				'image'                          => 'https://example.com/a.jpg',
				'x"><img src=x onerror=alert(1)>' => '1',
			],
			[],
			null
		);

		$this->assertSame(
			'background-color: #fff;background-image: url(https://example.com/a.jpg);',
			$result
		);
		$this->assertStringNotContainsString( '<img', $result );
	}

	public function testFormatSkipsNonStringProperties(): void {
		$result = RWMB_Background_Field::format_single_value(
			[],
			[
				'color'  => [ 'evil' ],
				'image'  => [ 'https://example.com/a.jpg' ],
				'repeat' => 'repeat',
			],
			[],
			null
		);

		$this->assertSame( 'background-repeat: repeat;', $result );
	}
}
