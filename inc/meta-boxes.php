<?php
/**
 * An object pool of all instantiated meta boxes that are ready to use later.
 *
 * @link    https://github.com/domnikl/DesignPatternsPHP/tree/master/Creational/Pool
 * @package Meta Box
 */

/**
 * Meta boxes pool class.
 */
class RWMB_Meta_Boxes {
	/**
	 * All meta box objects.
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Add a meta box object to the pool.
	 *
	 * @param RW_Meta_Box $meta_box Meta box instance.
	 */
	public static function add( RW_Meta_Box $meta_box ) {
		self::$instances[ $meta_box->id ] = $meta_box;
	}

	/**
	 * Retrieve a meta box instance by id.
	 *
	 * @param string $id A meta box instance id.
	 *
	 * @return RW_Meta_Box|bool False or meta box object instance.
	 */
	public static function get( $id ) {
		return isset( self::$instances[ $id ] ) ? self::$instances[ $id ] : false;
	}

	/**
	 * Retrieve all meta box instances.
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::$instances;
	}
}
