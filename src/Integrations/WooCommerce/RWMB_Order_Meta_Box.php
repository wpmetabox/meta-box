<?php

class RWMB_Order_Meta_Box extends RW_Meta_Box {
	protected $object_type = 'order';

	const SUPPORTED_ORDER_TYPES = [ 'shop_order', 'shop_subscription' ];

	public function register_fields() {
		$field_registry = rwmb_get_registry( 'field' );

		foreach ( $this->post_types as $post_type ) {
			foreach ( $this->fields as $field ) {
				$field_registry->add( $field, $post_type, $this->object_type );
			}
		}
	}

	protected function object_hooks() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Hide meta box if 'default_hidden'.
		add_filter( 'default_hidden_meta_boxes', [ $this, 'hide' ], 10, 2 );

		// HPOS doesn't fire save_post_{post_type}, use WooCommerce's hook(s) instead.
		// Register a separate save hook for EACH order type this field group targets.
		foreach ( $this->post_types as $post_type ) {
			$hook = $this->get_save_hook( $post_type );
			if ( $hook ) {
				add_action( $hook, [ $this, 'save_post' ] );
			}
		}
	}

	public function add_meta_boxes() {
		foreach ( $this->post_types as $post_type ) {
			$screen = $this->get_order_screen_id( $post_type );
			if ( ! $screen ) {
				continue;
			}

			add_filter( "postbox_classes_{$screen}_{$this->id}", [ $this, 'postbox_classes' ] );

			add_meta_box(
				$this->id,
				$this->title,
				[ $this, 'show' ],
				$screen,
				$this->context,
				$this->priority
			);
		}
	}

	public function is_edit_screen( $screen = null ) {
		if ( ! ( $screen instanceof WP_Screen ) ) {
			$screen = get_current_screen();
		}
		if ( ! $screen ) {
			return false;
		}

		foreach ( $this->post_types as $post_type ) {
			if ( $this->get_order_screen_id( $post_type ) === $screen->id ) {
				return true;
			}
		}

		return false;
	}

	protected function get_current_object_id() {
		if ( ! empty( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return absint( $_GET['id'] );
		}

		return parent::get_current_object_id();
	}

	public function save_post( $object_id ) {
		$object_id = absint( $object_id );
		if ( empty( $object_id ) ) {
			return;
		}

		parent::save_post( $object_id );

		// Flush only when saved successful
		if ( ! empty( $this->saved ) ) {
			$storage = $this->get_storage();
			if ( method_exists( $storage, 'flush' ) ) {
				$storage->flush( $object_id );
			}
		}
	}

	/**
	 * Get the screen ID for each order type.
	 */
	private function get_order_screen_id( string $post_type ) {
		if ( 'shop_order' === $post_type ) {
			return function_exists( 'wc_get_page_screen_id' )
				? wc_get_page_screen_id( 'shop-order' )
				: 'woocommerce_page_wc-orders';
		}

		if ( 'shop_subscription' === $post_type ) {
			return apply_filters(
				'rwmb_order_subscription_screen_id',
				'woocommerce_page_wc-orders--shop_subscription'
			);
		}

		return false;
	}

	/**
	 * Get the save hook for each order type.
	 */
	private function get_save_hook( string $post_type ) {
		if ( 'shop_order' === $post_type ) {
			return 'woocommerce_process_shop_order_meta';
		}

		if ( 'shop_subscription' === $post_type ) {
			return apply_filters(
				'rwmb_order_subscription_save_hook',
				'woocommerce_process_shop_subscription_meta'
			);
		}

		return false;
	}

	public static function normalize( $meta_box ) {
		$meta_box = parent::normalize( $meta_box );

		$post_types = array_values( array_intersect(
			(array) ( $meta_box['post_types'] ?? [] ),
			self::SUPPORTED_ORDER_TYPES
		) );
		$meta_box['post_types'] = $post_types ?: [ 'shop_order' ];

		return $meta_box;
	}

}