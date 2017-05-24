<?php
/**
 * Post storage
 *
 * @package Meta Box
 */

/**
 * Class RWMB_Post_Storage
 */
class RWMB_Post_Storage implements RWMB_Storage_Interface {

	/**
	 * Get value from storage.
	 *
	 * @param  int    $object_id Object id.
	 * @param  string $name      Field name.
	 * @param  array  $args      Custom arguments.
	 * @return mixed
	 */
	public function get( $object_id, $name, $args = array() ) {
		$single = ! empty( $args['single'] );
		return get_post_meta( $object_id, $name, $single );
	}
}
