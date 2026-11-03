<?php
use PHPUnit\Framework\TestCase;

class ReuseFieldGroupTest extends TestCase {
	private function expand( array $configs ): array {
		$expanded = ( new RWMB_Core() )->expand_field_groups( $configs );
		$this->assertIsArray( $expanded );

		return $expanded;
	}

	private function ids( array $fields ): array {
		return array_column( $fields, 'id' );
	}

	private function template(): array {
		return [
			'id'         => 'hero',
			'post_types' => [],
			'fields'     => [
				[
					'id'   => 'heading',
					'type' => 'text',
				],
				[
					'id'   => 'image',
					'type' => 'image',
				],
			],
		];
	}

	public function testConfigWithoutReferenceIsUnchanged() {
		$configs = [
			$this->template(),
			[
				'id'     => 'plain',
				'fields' => [ [ 'id' => 'a', 'type' => 'text' ] ],
			],
		];

		$this->assertSame( $configs, $this->expand( $configs ) );
	}

	public function testTopLevelReferenceIsReplacedByTemplateFields() {
		$configs = $this->expand( [
			$this->template(),
			[
				'id'     => 'page',
				'fields' => [
					[ 'id' => 'before', 'type' => 'text' ],
					[ 'use_field_group' => 'hero' ],
					[ 'id' => 'after', 'type' => 'text' ],
				],
			],
		] );

		$this->assertSame( [ 'before', 'heading', 'image', 'after' ], $this->ids( $configs[1]['fields'] ) );
	}

	public function testReferenceInsideGroupIsExpandedAndEachGroupKeepsItsOwnFields() {
		$configs = $this->expand( [
			$this->template(),
			[
				'id'     => 'page',
				'fields' => [
					[
						'id'     => 'left',
						'type'   => 'group',
						'fields' => [ [ 'use_field_group' => 'hero' ] ],
					],
					[
						'id'     => 'right',
						'type'   => 'group',
						'fields' => [ [ 'use_field_group' => 'hero' ] ],
					],
				],
			],
		] );

		$fields = $configs[1]['fields'];
		$this->assertSame( [ 'heading', 'image' ], $this->ids( $fields[0]['fields'] ) );
		$this->assertSame( [ 'heading', 'image' ], $this->ids( $fields[1]['fields'] ) );
	}

	public function testNestedTemplateIsExpanded() {
		$configs = $this->expand( [
			$this->template(),
			[
				'id'         => 'section',
				'post_types' => [],
				'fields'     => [
					[ 'use_field_group' => 'hero' ],
					[ 'id' => 'link', 'type' => 'url' ],
				],
			],
			[
				'id'     => 'page',
				'fields' => [ [ 'use_field_group' => 'section' ] ],
			],
		] );

		$this->assertSame( [ 'heading', 'image', 'link' ], $this->ids( $configs[2]['fields'] ) );
	}

	public function testSameTemplateCanBeUsedMoreThanOnceAtTheSameLevel() {
		$configs = $this->expand( [
			$this->template(),
			[
				'id'     => 'page',
				'fields' => [
					[ 'use_field_group' => 'hero' ],
					[ 'use_field_group' => 'hero' ],
				],
			],
		] );

		$this->assertSame( [ 'heading', 'image', 'heading', 'image' ], $this->ids( $configs[1]['fields'] ) );
	}

	public function testReferenceInsideGroupOfTemplateIsExpanded() {
		$configs = $this->expand( [
			$this->template(),
			[
				'id'         => 'card',
				'post_types' => [],
				'fields'     => [
					[
						'id'     => 'inner',
						'type'   => 'group',
						'fields' => [ [ 'use_field_group' => 'hero' ] ],
					],
				],
			],
			[
				'id'     => 'page',
				'fields' => [ [ 'use_field_group' => 'card' ] ],
			],
		] );

		$inner = $configs[2]['fields'][0];
		$this->assertSame( 'inner', $inner['id'] );
		$this->assertSame( [ 'heading', 'image' ], $this->ids( $inner['fields'] ) );
	}

	public function testTemplateIsFoundBySanitizedIdOrTitle() {
		$configs = $this->expand( [
			[
				'id'         => 'Hero Section',
				'post_types' => [],
				'fields'     => [ [ 'id' => 'from_id', 'type' => 'text' ] ],
			],
			[
				'title'      => 'Call To Action',
				'post_types' => [],
				'fields'     => [ [ 'id' => 'from_title', 'type' => 'text' ] ],
			],
			[
				'id'     => 'page',
				'fields' => [
					[ 'use_field_group' => 'hero-section' ],
					[ 'use_field_group' => 'Hero Section' ],
					[ 'use_field_group' => 'call-to-action' ],
				],
			],
		] );

		$this->assertSame( [ 'from_id', 'from_id', 'from_title' ], $this->ids( $configs[2]['fields'] ) );
	}

	public function testNullReferenceIsSkipped() {
		$configs = $this->expand( [
			[
				'id'     => 'page',
				'fields' => [
					[ 'use_field_group' => null ],
					[ 'id' => 'a', 'type' => 'text' ],
				],
			],
		] );

		$this->assertSame( [ 'a' ], $this->ids( $configs[0]['fields'] ) );
	}

	public function testNonStringReferenceIsSkipped() {
		$configs = $this->expand( [
			[
				'id'     => 'page',
				'fields' => [
					[ 'use_field_group' => [ 'hero' ] ],
					[ 'id' => 'a', 'type' => 'text' ],
				],
			],
		] );

		$this->assertSame( [ 'a' ], $this->ids( $configs[0]['fields'] ) );
	}

	public function testMissingTemplateIsSkipped() {
		$configs = $this->expand( [
			[
				'id'     => 'page',
				'fields' => [
					[ 'use_field_group' => 'nope' ],
					[ 'id' => 'a', 'type' => 'text' ],
				],
			],
		] );

		$this->assertSame( [ 'a' ], $this->ids( $configs[0]['fields'] ) );
	}

	public function testCircularReferenceIsSkipped() {
		$configs = $this->expand( [
			[
				'id'         => 'a',
				'post_types' => [],
				'fields'     => [
					[ 'id' => 'a_field', 'type' => 'text' ],
					[ 'use_field_group' => 'b' ],
				],
			],
			[
				'id'         => 'b',
				'post_types' => [],
				'fields'     => [
					[ 'id' => 'b_field', 'type' => 'text' ],
					[ 'use_field_group' => 'a' ],
				],
			],
			[
				'id'     => 'self',
				'fields' => [
					[ 'id' => 'own', 'type' => 'text' ],
					[ 'use_field_group' => 'self' ],
				],
			],
		] );

		$this->assertSame( [ 'a_field', 'b_field' ], $this->ids( $configs[0]['fields'] ) );
		$this->assertSame( [ 'b_field', 'a_field' ], $this->ids( $configs[1]['fields'] ) );
		$this->assertSame( [ 'own' ], $this->ids( $configs[2]['fields'] ) );
	}

	public function testInvalidConfigsAreLeftAlone() {
		$this->assertSame( 'x', ( new RWMB_Core() )->expand_field_groups( 'x' ) );
		$this->assertSame( [ 'x', [] ], ( new RWMB_Core() )->expand_field_groups( [ 'x', [] ] ) );
	}
}
