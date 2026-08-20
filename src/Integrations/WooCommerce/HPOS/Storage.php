<?php
namespace MetaBox\Integrations\WooCommerce\HPOS;

class Storage implements \RWMB_Storage_Interface {

	private $orders = [];

	private function get_order( $object_id ) {
		$object_id = absint( $object_id );
		if ( $object_id === 0 ) {
			return null;
		}

		if ( ! isset( $this->orders[ $object_id ] ) ) {
			$this->orders[ $object_id ] = wc_get_order( $object_id );
		}
		return $this->orders[ $object_id ];
	}

	public function get( $object_id, $name, $args = [] ) {
		$single = is_array( $args ) ? ! empty( $args['single'] ) : (bool) $args;

		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return $single ? '' : [];
		}

		if ( $single ) {
			return $order->get_meta( $name, true );
		}

		return array_map( fn( $meta ) => $meta->value, $order->get_meta( $name, false ) );
	}

	public function add( $object_id, $name, $value, $unique = false ): bool {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		$order->add_meta_data( $name, wp_unslash( $value ), $unique );
		return true;
	}

	public function update( $object_id, $name, $value, $prev_value = '' ): bool {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		if ( '' !== $prev_value ) {
			return $this->update_first_matching_meta( $order, $name, $prev_value, wp_unslash( $value ) );
		}

		$order->update_meta_data( $name, wp_unslash( $value ) );
		return true;
	}

	private function update_first_matching_meta( \WC_Order $order, string $name, $prev_value, $new_value ): bool {
		foreach ( $order->get_meta( $name, false ) as $meta ) {
			if ( $meta->value == $prev_value ) {
				$meta->value = $new_value;
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete metadata.
	 *
	 * Note: The $delete_all parameter is intentionally ignored. Order meta is inherently
	 * per-order, and WC_Order::delete_meta_data() only operates on a single order object.
	 */
	public function delete( $object_id, $name, $value = '', $delete_all = false ): bool {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		if ( $value !== '' ) {
			$order->delete_meta_data_value( $name, wp_unslash( $value ) );
		} else {
			$order->delete_meta_data( $name );
		}

		return true;
	}

	public function flush( $object_id ): void {
		$object_id = absint( $object_id );
		if ( ! empty( $this->orders[ $object_id ] ) ) {
			$this->orders[ $object_id ]->save();
		}
	}
}
