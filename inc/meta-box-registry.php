<?php
/**
 * A registry for storing all meta boxes.
 *
 * @link    https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 * @package Meta Box
 */

/**
 * Meta box registry class.
 */
class RWMB_Meta_Box_Registry {
	/**
	 * Internal data storage.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Add a meta box to the registry.
	 *
	 * @param RW_Meta_Box $meta_box Meta box instance.
	 */
	public function add( RW_Meta_Box $meta_box ) {
		$this->data[ $meta_box->id ] = $meta_box;
	}

	/**
	 * Retrieve a meta box by id.
	 *
	 * @param string $id Meta box id.
	 *
	 * @return RW_Meta_Box|bool False or meta box object.
	 */
	public function get( $id ) {
		return isset( $this->data[ $id ] ) ? $this->data[ $id ] : false;
	}

	/**
	 * Retrieve all meta boxes.
	 *
	 * @return array
	 */
	public function all() {
		return $this->data;
	}
}
