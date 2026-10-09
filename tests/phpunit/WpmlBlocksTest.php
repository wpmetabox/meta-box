<?php
use PHPUnit\Framework\TestCase;

class WpmlBlocksTest extends TestCase {
	/**
	 * Get the WPML config of a block, or null when the block is not declared.
	 */
	protected function getBlockConfig( string $meta_box_id ) {
		$wpml   = new \MetaBox\Integrations\WPML();
		$config = $wpml->add_blocks_config( [ 'wpml-config' => [] ] );

		foreach ( $config['wpml-config']['gutenberg-blocks']['gutenberg-block'] ?? [] as $block ) {
			if ( 'meta-box/' . $meta_box_id === $block['attr']['type'] ) {
				return $block;
			}
		}

		return null;
	}

	/**
	 * Get the field names of WPML keys, with their sub keys.
	 */
	protected function getKeyNames( array $keys ): array {
		$names = [];
		foreach ( $keys as $key ) {
			$name           = $key['attr']['name'];
			$names[ $name ] = isset( $key['key'] ) ? $this->getKeyNames( $key['key'] ) : true;
		}
		return $names;
	}

	public function testBlockDeclaresOnlyTextFields() {
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'wpml-test-team',
			'type'   => 'block',
			'fields' => [
				[ 'id' => 'title', 'type' => 'text' ],
				[ 'id' => 'layout', 'type' => 'select', 'options' => [ 'grid' => 'Grid' ] ],
				[ 'id' => 'background', 'type' => 'color' ],
				[ 'id' => 'columns', 'type' => 'number' ],
				[ 'id' => 'tags', 'type' => 'text', 'clone' => true ],
				[
					'id'     => 'members',
					'type'   => 'group',
					'clone'  => true,
					'fields' => [
						[ 'id' => 'photo', 'type' => 'single_image' ],
						[ 'id' => 'name', 'type' => 'text' ],
						[ 'id' => 'position', 'type' => 'text' ],
						[ 'id' => 'bio', 'type' => 'wysiwyg' ],
					],
				],
				[
					'id'     => 'settings',
					'type'   => 'group',
					'fields' => [
						[ 'id' => 'align', 'type' => 'radio', 'options' => [ 'left' => 'Left' ] ],
					],
				],
			],
		] );

		$block = $this->getBlockConfig( 'wpml-test-team' );

		$this->assertNotNull( $block );
		$this->assertSame( '1', $block['attr']['translate'] );
		$this->assertSame(
			[
				'data' => [
					'title'   => true,
					'tags'    => [ '*' => true ],
					'members' => [
						'*' => [
							'name'     => true,
							'position' => true,
							'bio'      => true,
						],
					],
				],
			],
			$this->getKeyNames( $block['key'] )
		);
	}

	public function testBlockWithoutTextFieldsHasNothingToTranslate() {
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'wpml-test-spacer',
			'type'   => 'block',
			'fields' => [
				[ 'id' => 'height', 'type' => 'number' ],
				[ 'id' => 'style', 'type' => 'select', 'options' => [ 'line' => 'Line' ] ],
			],
		] );

		$block = $this->getBlockConfig( 'wpml-test-spacer' );

		$this->assertNotNull( $block );
		$this->assertSame( '0', $block['attr']['translate'] );
		$this->assertArrayNotHasKey( 'key', $block );
	}

	public function testPostMetaBoxIsNotDeclared() {
		rwmb_get_registry( 'meta_box' )->make( [
			'id'     => 'wpml-test-post',
			'fields' => [
				[ 'id' => 'subtitle', 'type' => 'text' ],
			],
		] );

		$this->assertNull( $this->getBlockConfig( 'wpml-test-post' ) );
	}
}
