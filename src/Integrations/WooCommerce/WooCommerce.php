<?php
namespace MetaBox\Integrations\WooCommerce;

/**
 * Register logic to 'woocommerce_loaded' hook
 */
class WooCommerce {
	private $order_type_cache = [];

	public function __construct() {
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );

		// Defensive reload
		if ( did_action( 'woocommerce_loaded' ) ) {
			$this->register();
		}
	}

	public function register() {
		add_filter( 'rwmb_meta_box_class_name', [ $this, 'maybe_swap_meta_box_class' ], 10, 2 );
		add_filter( 'rwmb_meta_type', [ $this, 'force_order_meta_type' ], 10, 3 );
	}

	public function maybe_swap_meta_box_class( $class_name, $settings ) {
		if ( 'order' !== ( $settings['type'] ?? '' ) ) {
			return $class_name;
		}

		return 'RWMB_Order_Meta_Box';
	}

	public function force_order_meta_type( $type, $object_type, $object_id ) {
		if ( 'order' !== $object_type ) {
			return $type;
		}

		$object_id = absint( $object_id );
		if ( isset( $this->order_type_cache[ $object_id ] ) ) {
			return $this->order_type_cache[ $object_id ];
		}

		$order = function_exists( 'wc_get_order' ) ? wc_get_order( $object_id ) : false;
		$resolved_type = $order ? $order->get_type() : $type;

		$this->order_type_cache[ $object_id ] = $resolved_type;

		return $resolved_type;
	}
}
