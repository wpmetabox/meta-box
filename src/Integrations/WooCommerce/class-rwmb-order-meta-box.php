<?php
/**
 * RW_Meta_Box subclass dedicated to WooCommerce Orders when HPOS is enabled (full mode, no post sync).
 */
class RWMB_Order_Meta_Box extends RW_Meta_Box {

	/**
	 * Object type = 'order' so rwmb_get_storage() automatically resolves RWMB_Order_Storage.
	 */
	protected $object_type = 'order';

	protected function object_hooks() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_filter( 'default_hidden_meta_boxes', [ $this, 'hide' ], 10, 2 );

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
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		return $id ?: parent::get_current_object_id();
	}

	public function save_post( $object_id ) {
		parent::save_post( $object_id );

		$storage = $this->get_storage();
		if ( method_exists( $storage, 'flush' ) ) {
			$storage->flush( $this->object_id );
		}
	}

	private function get_order_screen_id(): string {
		return function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'woocommerce_page_wc-orders';
	}
}