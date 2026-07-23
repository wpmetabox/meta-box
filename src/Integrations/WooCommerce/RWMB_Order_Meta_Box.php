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
		// Đăng ký hook lưu riêng cho TỪNG order type mà field group này target.
		// Register a separate save hook for EACH order type this field group targets.
		foreach ( $this->post_types as $post_type ) {
			$hook = $this->get_save_hook( $post_type );
			if ( $hook ) {
				add_action( $hook, [ $this, 'save_post' ] );
			}
		}
	}

	public function add_meta_boxes() {
		// Đăng ký meta box lên MỌI screen tương ứng với từng order type field
		// group này target (vd vừa shop_order vừa shop_subscription).
		// Register the meta box on EVERY screen matching each order type this
		// field group targets (e.g. both shop_order and shop_subscription).
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
	 * Lấy screen ID cho từng order type.
	 *
	 * 'shop_order': ĐÃ XÁC NHẬN qua wc_get_page_screen_id('shop-order').
	 * 'shop_subscription': CHƯA XÁC NHẬN - đang đoán theo pattern
	 * 'woocommerce_page_wc-orders--{order_type}' của HPOS multi-order-type.
	 * Cần verify lại bằng debug (current_screen->id thực tế) trước khi dùng
	 * production.
	 *
	 * Get the screen ID for each order type.
	 *
	 * 'shop_order': CONFIRMED via wc_get_page_screen_id('shop-order').
	 * 'shop_subscription': NOT YET CONFIRMED - guessing based on the
	 * 'woocommerce_page_wc-orders--{order_type}' HPOS multi-order-type pattern.
	 * Needs verification via debug (actual current_screen->id) before
	 * production use.
	 *
	 * @param string $post_type Order type (shop_order, shop_subscription...).
	 * @return string|false Screen ID, or false if order type not supported.
	 */
	private function get_order_screen_id( string $post_type ) {
		if ( 'shop_order' === $post_type ) {
			return function_exists( 'wc_get_page_screen_id' )
				? wc_get_page_screen_id( 'shop-order' )
				: 'woocommerce_page_wc-orders';
		}

		if ( 'shop_subscription' === $post_type ) {
			// TODO: xác nhận lại giá trị này bằng debug snippet trước khi dùng
			// production - đây mới chỉ là suy đoán theo pattern HPOS.
			// TODO: confirm this value via debug snippet before production use -
			// this is currently only a guess based on the HPOS pattern.
			return apply_filters(
				'rwmb_order_subscription_screen_id',
				'woocommerce_page_wc-orders--shop_subscription'
			);
		}

		return false;
	}

	/**
	 * Lấy hook lưu dữ liệu cho từng order type.
	 *
	 * 'shop_order': ĐÃ XÁC NHẬN - woocommerce_process_shop_order_meta, do
	 * WC_Meta_Box_Order_Data::save() (core WooCommerce) fire.
	 * 'shop_subscription': CHƯA XÁC NHẬN có tồn tại hook tương tự do Woo
	 * Subscriptions tự fire hay không.
	 *
	 * Get the save hook for each order type.
	 *
	 * 'shop_order': CONFIRMED - woocommerce_process_shop_order_meta, fired by
	 * WC_Meta_Box_Order_Data::save() (WooCommerce core).
	 * 'shop_subscription': NOT YET CONFIRMED whether an analogous hook is fired
	 * by WooCommerce Subscriptions itself.
	 *
	 * @param string $post_type Order type.
	 * @return string|false Hook name, or false if unknown.
	 */
	private function get_save_hook( string $post_type ) {
		if ( 'shop_order' === $post_type ) {
			return 'woocommerce_process_shop_order_meta';
		}

		if ( 'shop_subscription' === $post_type ) {
			// TODO: xác nhận hook này có thực sự được Woo Subscriptions fire
			// không - nếu không, field group trên subscription sẽ hiện nhưng
			// KHÔNG LƯU được, cần tìm hook đúng.
			// TODO: confirm this hook is actually fired by WooCommerce
			// Subscriptions - if not, the field group will show but NOT SAVE
			// on subscriptions, need to find the correct hook.
			return apply_filters(
				'rwmb_order_subscription_save_hook',
				'woocommerce_process_shop_subscription_meta'
			);
		}

		return false;
	}

	public static function normalize( $meta_box ) {
		$meta_box = parent::normalize( $meta_box );

		// Chỉ giữ lại post_types nằm trong whitelist order type hợp lệ.
		// Only keep post_types that are in the valid order type whitelist.
		$post_types = array_values( array_intersect(
			(array) ( $meta_box['post_types'] ?? [] ),
			self::SUPPORTED_ORDER_TYPES
		) );

		// Mặc định 'shop_order' nếu không khai báo gì hợp lệ - giữ tương thích
		// ngược với field group cũ (post_types => 'shop_order' hoặc rỗng).
		// Default to 'shop_order' if nothing valid was declared - keeps backward
		// compatibility with older field groups (post_types => 'shop_order' or empty).
		$meta_box['post_types'] = $post_types ?: [ 'shop_order' ];

		return $meta_box;
	}

}