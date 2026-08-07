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
		if ( $single ) {
			return $order->get_meta( $name, true );
		}

		// WC Core uses single=false for multiple/clone_as_multiple fields (inc/field.php:139-145), without map, those fields receive objects instead of values, breaking render/save.
		return array_map( fn( $meta ) => $meta->value, $order->get_meta( $name, false ) );
	}

	public function add( $object_id, $name, $value, $unique = false ): bool {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		// WC add_meta_data() returns void - so failure cannot be detected via return value.
		// However there's no real silent fail when $unique=true, WC first deletes all existing entries then always adds the new one (abstract-wc-data.php:499-507)
		// Returning true is correct.
		$order->add_meta_data( $name, $value, $unique );
		return true;
	}

	public function update( $object_id, $name, $value, $prev_value = '' ): bool {
		$order = $this->get_order( $object_id );
		if ( ! $order ) {
			return false;
		}

		if ( '' !== $prev_value ) {
			return $this->update_first_matching_meta( $order, $name, $prev_value, $value );
		}

		$order->update_meta_data( $name, $value );
		return true;
	}

	/**
	 * Updates only the FIRST entry in meta_data with key=$name and value=$prev_value.
	 */
	private function update_first_matching_meta( \WC_Order $order, string $name, $prev_value, $new_value ): bool {
		foreach ( $order->get_meta( $name, false ) as $meta ) {
			// Use maybe_unserialize for type-safe comparison (array === array)
			if ( maybe_unserialize( $meta->value ) === maybe_unserialize( $prev_value ) ) {
				$meta->value = $new_value;
				return true;
			}
		}

		return false;
	}

	public function delete( $object_id, $name, $value = '', $delete_all = false ): bool {
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

	public function flush( $object_id ): void {
		$object_id = absint( $object_id );
		if ( ! empty( $this->orders[ $object_id ] ) ) {
			$this->orders[ $object_id ]->save();
		}
	}
}
