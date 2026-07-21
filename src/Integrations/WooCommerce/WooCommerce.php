<?php
namespace MetaBox\Integrations\WooCommerce;

/**
 * Register logic to 'woocommerce_loaded' hook
 */
class WooCommerce {
	public function __construct() {
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );

		// Defensive reload
		if ( did_action( 'woocommerce_loaded' ) ) {
			$this->register();
		}
	}
 
	public function register() {
		add_filter( 'rwmb_meta_box_class_name', [ $this, 'maybe_swap_meta_box_class' ], 10, 2 );
	}

	public function maybe_swap_meta_box_class( $class_name, $settings ) {
		if ( 'order' !== ( $settings['type'] ?? '' ) ) {
			return $class_name;
		}
 
		return 'RWMB_Order_Meta_Box';
	}

}