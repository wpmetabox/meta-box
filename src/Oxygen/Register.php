<?php
namespace MetaBox\Oxygen;

class Register {

	public function __construct() {
		add_action( 'oxygen_add_plus_sections', [ $this, 'add_metabox_category' ] );
	}

	public function add_metabox_category() {
		if ( ! defined( 'CT_VERSION' ) ) {
			return;
		}
		\CT_Toolbar::oxygen_add_plus_accordion_section( 'meta-box', esc_html__( 'Meta Box', 'meta-box' ) );
	}
}
