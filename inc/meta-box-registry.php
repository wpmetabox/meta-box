<?php
/**
 * A registry for storing all instantiated meta boxes.
 *
 * @link    https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 * @package Meta Box
 */

/**
 * Meta box registry class.
 */
class RWMB_Meta_Box_Registry {
	/**
	 * All meta box objects.
	 *
	 * @var array
	 */
	protected $instances = array();

	/**
	 * Add a meta box object to the pool.
	 *
	 * @param RW_Meta_Box $meta_box Meta box instance.
	 */
	public function add( RW_Meta_Box $meta_box ) {
		$this->instances[ $meta_box->id ] = $meta_box;
	}

	/**
	 * Retrieve a meta box instance by id.
	 *
	 * @param string $id A meta box instance id.
	 *
	 * @return RW_Meta_Box|bool False or meta box object instance.
	 */
	public function get( $id ) {
		return isset( $this->instances[ $id ] ) ? $this->instances[ $id ] : false;
	}

	/**
	 * Retrieve all meta box instances.
	 *
	 * @return array
	 */
	public function all() {
		return $this->instances;
	}
}
