<?php
namespace MetaBox\Integrations\WooCommerce;

class WooCommerce {
	public function __construct() {
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );
	}
 
	public function register() {
		add_filter( 'rwmb_meta_box_class_name', [ $this, 'maybe_swap_meta_box_class' ], 10, 2 );
 
		add_filter( 'rwmb_meta_type', [ $this, 'force_order_meta_type' ], 10, 2 );
	}
 
	public function force_order_meta_type( $type, $object_type ) {
		return 'order' === $object_type ? 'shop_order' : $type;
	}

	public function maybe_swap_meta_box_class( $class_name, $settings ) {
		if ( 'order' !== ( $settings['object_type'] ?? '' ) ) {
			return $class_name;
		}
 
		return 'RWMB_Order_Meta_Box';
	}

}