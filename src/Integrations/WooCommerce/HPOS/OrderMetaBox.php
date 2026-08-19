<?php
namespace MetaBox\Integrations\WooCommerce\HPOS;

class OrderMetaBox extends \RW_Meta_Box {
	private $storage;

	const SUPPORTED_ORDER_TYPES = [ 'shop_order', 'shop_subscription' ];
	const SAVE_HOOKS = [
		'shop_order'        => 'woocommerce_process_shop_order_meta',
		'shop_subscription' => 'woocommerce_process_shop_subscription_meta',
	];

	protected function object_hooks() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Hide meta box if 'default_hidden'.
		add_filter( 'default_hidden_meta_boxes', [ $this, 'hide' ], 10, 2 );

		// HPOS doesn't fire save_post_{post_type}, use WooCommerce's hook(s) instead.
		foreach ( $this->post_types as $post_type ) {
			add_action( self::SAVE_HOOKS[ $post_type ], [ $this, 'save_post' ] );
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
		if ( ! ( $screen instanceof \WP_Screen ) ) {
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return empty( $_GET['id'] ) ? parent::get_current_object_id() : absint( $_GET['id'] );
	}

	public function save_post( $object_id ) {
		$object_id = absint( $object_id );
		if ( empty( $object_id ) ) {
			return;
		}

		parent::save_post( $object_id );

		if ( empty( $this->saved ) ) {
			return;
		}

		$storage = $this->get_storage();
		$storage->flush( $object_id );
	}

	public function get_storage() {
		if ( ! $this->storage ) {
			$this->storage = new OrderStorage();
		}

		return $this->storage;
	}

	private function get_order_screen_id( string $post_type ): string {
		return in_array( $post_type, self::SUPPORTED_ORDER_TYPES, true ) ? (string) wc_get_page_screen_id( $post_type ) : '';
	}

	public static function normalize( $meta_box ) {
		$meta_box = parent::normalize( $meta_box );

		$meta_box['post_types'] = array_values( array_intersect(
			(array) ( $meta_box['post_types'] ?? [] ),
			self::SUPPORTED_ORDER_TYPES
		) );

		return $meta_box;
	}
}
