<?php
namespace MetaBox\Integrations\WooCommerce;

class WooCommerce {
	private $order_storage;
	private $order_type_cache = [];

	/**
	 * Register logic to 'woocommerce_loaded' hook
	 */
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

		add_filter( 'rwmb_get_storage', [ $this, 'maybe_swap_storage' ], 10, 2 );

		add_action( 'rwmb_flush_data', [ $this, 'flush_order_storage' ], 10, 3 );
	}

	public function maybe_swap_storage( $storage, $object_type ) {
		if ( 'order' !== $object_type ) {
			return $storage;
		}

		if ( ! $this->order_storage ) {
			$this->order_storage = new OrderStorage();
		}

		return $this->order_storage;
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

	public function maybe_swap_meta_box_class( $class_name, $settings ) {
		if ( 'order' !== ( $settings['type'] ?? '' ) ) {
			return $class_name;
		}

		return OrderMetaBox::class;
	}

	public function flush_order_storage( $object_id, $field, $args ) {
		if ( ( $args['object_type'] ?? '' ) !== 'order' ) {
			return;
		}

		if ( $this->order_storage ) {
			$this->order_storage->flush( $object_id );
		}
	}

}
