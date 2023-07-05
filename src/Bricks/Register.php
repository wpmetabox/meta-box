<?php
namespace MetaBox\Bricks;

class Register {
	public function __construct() {
		add_filter( 'bricks/builder/i18n', [ $this, 'i18n' ] );
	}

	public function i18n( array $i18n ): array {
		$i18n['meta-box'] = esc_html__( 'Meta Box', 'mb-frontend-submission' );
		return $i18n;
	}
}
