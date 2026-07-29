<?php
namespace MetaBox\Integrations\WooCommerce;

class OrderStorage implements \RWMB_Storage_Interface {

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
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return '';
		}

		$single = is_array( $args ) ? ! empty( $args['single'] ) : (bool) $args;
		return $order->get_meta( $name, $single );
	}

	public function add( $object_id, $name, $value, $unique = false ) {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}
		$order->add_meta_data( $name, $value, $unique );
		return true;
	}

	public function update( $object_id, $name, $value, $prev_value = '' ) {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		if ( '' !== $prev_value ) {
			if ( ! $this->has_meta_value( $order, $name, $prev_value ) ) {
				return false;
			}

			$order->delete_meta_data_value( $name, $prev_value );

			if ( ! $this->has_meta_value( $order, $name, $value ) ) {
				$order->add_meta_data( $name, $value );
			}
		} else {
			$order->update_meta_data( $name, $value );
		}

		return true;
	}

	private function has_meta_value( \WC_Order $order, string $name, $value ): bool {
		foreach ( (array) $order->get_meta( $name, false ) as $meta ) {
			if ( (string) $meta->value === (string) $value ) {
				return true;
			}
		}

		return false;
	}

	public function delete( $object_id, $name, $value = '', $delete_all = false ) {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		if ( $value !== '' ) {
			$order->delete_meta_data_value( $name, $value );
		} else {
			$order->delete_meta_data( $name );
		}

		return true;
	}

	public function flush( $object_id ) {
		$object_id = absint( $object_id );
		if ( isset( $this->orders[ $object_id ] ) && $this->orders[ $object_id ] ) {
			$this->orders[ $object_id ]->save();
		}
	}
}