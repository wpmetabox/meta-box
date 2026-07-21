<?php

class RWMB_Order_Meta_Box extends RW_Meta_Box {
	protected $type = 'order';

	public function register_fields() {
		$field_registry = rwmb_get_registry( 'field' );

		foreach ( $this->post_types as $post_type ) {
			foreach ( $this->fields as $field ) {
				$field_registry->add( $field, $post_type, $this->type );
			}
		}
	}

	protected function object_hooks() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Hide meta box if 'default_hidden'.
		add_filter( 'default_hidden_meta_boxes', [ $this, 'hide' ], 10, 2 );

		// HPOS doesn't fire save_post_{post_type}, use WooCommerce's hook instead.
		add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save_post' ] );
	}

	public function add_meta_boxes() {
		$screen = $this->get_order_screen_id();
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

	public function is_edit_screen( $screen = null ) {
		if ( ! ( $screen instanceof WP_Screen ) ) {
			$screen = get_current_screen();
		}

		return $screen && $this->get_order_screen_id() === $screen->id;
	}

	protected function get_current_object_id() {
		if ( ! empty( $_GET['id'] ) ) { // phpcs:ignore
			return absint( $_GET['id'] );
		}

		return parent::get_current_object_id();
	}

	public function save_post( $object_id ) {
		parent::save_post( $object_id );

		$storage = $this->get_storage();

		if ( method_exists( $storage, 'flush' ) ) {
			$storage->flush( $object_id );
		}
	}

	private function get_order_screen_id(): string {
		return function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders';
	}

	public static function normalize( $meta_box ) {
		$meta_box                = parent::normalize( $meta_box );
		$meta_box['post_types']  = [ 'shop_order' ];
		return $meta_box;
	}

}