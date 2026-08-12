<?php
namespace MetaBox\Integrations\WooCommerce\HPOS;

class Manager {
	private $order_storage;
	private $order_type_cache = [];

	public function __construct() {
		add_action( 'before_woocommerce_init', [ $this, 'declare_hpos_compatibility' ] );
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );
	}

	public function declare_hpos_compatibility(): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				trailingslashit( RWMB_DIR ) . 'meta-box.php',
				true
			);
		}
	}

	public function register(): void {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		add_filter( 'rwmb_meta_box_class_name', [ $this, 'maybe_swap_meta_box_class' ], 10, 2 );

		add_filter( 'rwmb_meta_type', [ $this, 'force_order_meta_type' ], 10, 3 );

		add_filter( 'rwmb_get_storage', [ $this, 'maybe_swap_storage' ], 10, 2 );

		add_action( 'rwmb_flush_data', [ $this, 'flush_order_storage' ], 10, 3 );
	}

	private function is_hpos_enabled(): bool {
		return class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	public function maybe_swap_meta_box_class( string $class_name, array $settings ): string {
		$post_types  = (array) ( $settings['post_types'] ?? [] );
		$order_types = OrderMetaBox::SUPPORTED_ORDER_TYPES;

		return empty( array_intersect( $post_types, $order_types ) ) ? $class_name : OrderMetaBox::class;
	}

	public function maybe_swap_storage( $storage, string $object_type ) {
		if ( ! $this->order_storage ) {
			$this->order_storage = new OrderStorage();
		}

		return $this->order_storage;
	}

	public function force_order_meta_type( string $type, string $object_type, $object_id ): string {
		$object_id = absint( $object_id );
		if ( isset( $this->order_type_cache[ $object_id ] ) ) {
			return $this->order_type_cache[ $object_id ];
		}

		$order = wc_get_order( $object_id );
		$resolved_type = $order ? $order->get_type() : $type;

		$this->order_type_cache[ $object_id ] = $resolved_type;

		return $resolved_type;
	}

	public function flush_order_storage( $object_id, array $field, array $args ): void {
		if ( $this->order_storage ) {
			$this->order_storage->flush( $object_id );
		}
	}

}
