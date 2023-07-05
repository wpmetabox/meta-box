<?php
namespace MetaBox\Elementor;

class Register {
	public function __construct() {
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_metabox_category' ] );
	}

	public function add_metabox_category() {
		\Elementor\Plugin::instance()->elements_manager->add_category(
			'metabox',
			[
				'title' => esc_html__( 'Meta Box', 'meta-box' ),
				'icon'  => 'fa fa-m',
			]
		);
	}
}
