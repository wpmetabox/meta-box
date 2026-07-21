<?php

class RWMB_Order_Storage implements RWMB_Storage_Interface {

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

		if ( $prev_value !== '' ) {
			$order->delete_meta_data_value( $name, $prev_value );
			$order->add_meta_data( $name, $value );
		} else {
			$order->update_meta_data( $name, $value );
		}

		return true;
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

		// Ignore $delete_all because storage only run in 1 order
		return true;
	}

	/**
	 * Persist all queued meta_data changes to the DB.
	 */
	public function flush( $object_id ) {
		$object_id = absint( $object_id );
		if ( isset( $this->orders[ $object_id ] ) && $this->orders[ $object_id ] ) {
			$this->orders[ $object_id ]->save();
		}
	}
}