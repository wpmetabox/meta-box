<?php
namespace MetaBox\Integrations\WooCommerce\HPOS;

class Manager {
	private $order_type_cache = [];

	public function __construct() {
		add_action( 'before_woocommerce_init', [ $this, 'declare_hpos_compatibility' ] );
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );
		if ( did_action( 'woocommerce_loaded' ) ) {
			$this->register();
		}
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
		if ( $this->is_hpos_enabled() ) {
			add_filter( 'rwmb_meta_box_class_name', [ $this, 'change_meta_box_class_name' ], 10, 2 );
		}
	}

	private function is_hpos_enabled(): bool {
		return class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	public function change_meta_box_class_name( string $class_name, array $settings ): string {
		$post_types  = (array) ( $settings['post_types'] ?? [] );
		$order_types = MetaBox::SUPPORTED_ORDER_TYPES;

		return empty( array_intersect( $post_types, $order_types ) ) ? $class_name : MetaBox::class;
	}
}
