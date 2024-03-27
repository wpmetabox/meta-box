<?php
namespace MetaBox\Integrations;

class Block {
	public function __construct() {
		add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
	}

	public function register_block_category( $categories ) {
		$categories[] = [
			'slug'  => 'meta-box',
			'title' => esc_html__( 'Meta Box', 'meta-box' ),
		];

		return $categories;
	}
}