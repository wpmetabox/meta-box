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
	 * Create a meta box object.
	 *
	 * @param array $settings Meta box settings.
	 * @return \RW_Meta_Box
	 */
	public function make( $settings ) {
		$class_name = apply_filters( 'rwmb_meta_box_class_name', 'RW_Meta_Box', $settings );

		$meta_box = new $class_name( $settings );
		$this->add( $meta_box );
		return $meta_box;
	}

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
	 * Get meta boxes under some conditions.
	 *
	 * @param array $args Custom argument to get meta boxes by.
	 *
	 * @return array
	 */
	public function get_by( $args ) {
		$meta_boxes = $this->data;
		foreach ( $meta_boxes as $index => $meta_box ) {
			foreach ( $args as $key => $value ) {
				$meta_box_key = 'object_type' === $key ? $meta_box->get_object_type() : $meta_box->$key;
				if ( $meta_box_key !== $value ) {
					unset( $meta_boxes[ $index ] );
					continue 2; // Skip the meta box loop.
				}
			}
		}

		return $meta_boxes;
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
