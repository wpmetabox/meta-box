<?php
namespace MetaBox\Integrations\WooCommerce;

use Automattic\WooCommerce\Utilities\OrderUtil;

class WooCommerce {
	private $fields = [];

	public function __construct() {
		add_action( 'woocommerce_loaded', [ $this, 'register' ] );
	}

	public function register() {
		if ( ! $this->is_hpos_enabled() ) {
			return;
		}

		add_filter( 'rwmb_meta_boxes', [ $this, 'collect_admin_column_fields' ], 20 );
		add_filter( 'woocommerce_shop_order_list_table_columns', [ $this, 'register_columns' ] );
		add_action( 'woocommerce_shop_order_list_table_custom_column', [ $this, 'render_column' ], 10, 2 );
	}

	private function is_hpos_enabled(): bool {
		return class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Quét toàn bộ field group cho shop_order, gom field nào bật admin_columns.
	 * Scan all field groups for shop_order, collect fields with admin_columns enabled.
	 */
	public function collect_admin_column_fields( $meta_boxes ) {
		foreach ( $meta_boxes as $meta_box ) {
			$post_types = (array) ( $meta_box['post_types'] ?? [] );
			if ( ! in_array( 'shop_order', $post_types, true ) ) {
				continue;
			}

			foreach ( $meta_box['fields'] ?? [] as $field ) {
				if ( empty( $field['admin_columns'] ) ) {
					continue;
				}

				$this->fields[ $field['id'] ] = [
					'label' => $field['name'] ?? $field['id'],
					'type'  => $field['type'] ?? 'text',
				];
			}
		}

		// Không sửa gì $meta_boxes, chỉ dùng làm "tai nghe" config
		// We don't modify $meta_boxes, only use it to "listen" to the config
		return $meta_boxes;
	}

	public function register_columns( $columns ) {
		foreach ( $this->fields as $id => $field ) {
			$columns[ $id ] = esc_html( $field['label'] );
		}
		return $columns;
	}

	public function render_column( $column, $order ) {
		if ( ! isset( $this->fields[ $column ] ) ) {
			return;
		}

		$value = $order->get_meta( $column );

		// TODO: nếu field type là select/checkbox/image... cần format riêng thay vì echo thẳng
		// TODO: if field type is select/checkbox/image..., needs specific formatting instead of plain echo
		echo esc_html( is_array( $value ) ? implode( ', ', $value ) : $value );
	}
}