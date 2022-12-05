<?php
/**
 * A registry for storing all meta boxes.
 *
 * @link https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 */
class RWMB_Meta_Box_Registry {
	private $data = [];

	/**
	 * Create a meta box object.
	 *
	 * @param array $settings Meta box settings.
	 * @return \RW_Meta_Box
	 */
	public function make( array $settings ) {
		$class_name = apply_filters( 'rwmb_meta_box_class_name', 'RW_Meta_Box', $settings );

		$meta_box = new $class_name( $settings );
		$this->add( $meta_box );
		return $meta_box;
	}

	public function add( RW_Meta_Box $meta_box ) {
		$this->data[ $meta_box->id ] = $meta_box;
	}

	public function get( $id ) {
		return $this->data[ $id ] ?? false;
	}

	/**
	 * Get meta boxes under some conditions.
	 *
	 * @param array $args Custom argument to get meta boxes by.
	 */
	public function get_by( array $args ) : array {
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

	public function all() {
		return $this->data;
	}
}
