<?php
/**
 * A registry for storing all fields.
 *
 * @link    https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 * @package Meta Box
 */

/**
 * Field registry class.
 */
class RWMB_Field_Registry {
	/**
	 * Internal data storage.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Add all fields in a meta box to the registry.
	 *
	 * @param RW_Meta_Box $meta_box Meta box object.
	 */
	public function add_from_meta_box( RW_Meta_Box $meta_box ) {
		foreach ( $meta_box->fields as $field ) {
			foreach ( $meta_box->post_types as $post_type ) {
				$this->add( $field, $post_type );
			}
		}
	}

	/**
	 * Add a single field to the registry.
	 *
	 * @param array  $field       Field configuration.
	 * @param string $post_type   Post type|Taxonomy|'user'|Setting page which the field belongs to.
	 * @param string $object_type Object type which the field belongs to.
	 */
	public function add( $field, $post_type, $object_type = null ) {
		if ( ! isset( $field['id'] ) ) {
			return;
		}

		if ( ! $object_type ) {
			$object_type = 'post';
		}

		if ( empty( $this->data[ $object_type ][ $post_type ] ) ) {
			$this->data[ $object_type ][ $post_type ] = array();
		}
		$this->data[ $object_type ][ $post_type ][ $field['id'] ] = $field;
	}

	/**
	 * Retrieve a field.
	 *
	 * @param string $id          A meta box instance id.
	 * @param string $post_type   Post type|Taxonomy|'user'|Setting page which the field belongs to.
	 * @param string $object_type Object type which the field belongs to.
	 *
	 * @return bool|array False or field configuration.
	 */
	public function get( $id, $post_type, $object_type = null ) {
		if ( ! $object_type ) {
			$object_type = 'post';
		}

		return isset( $this->data[ $object_type ][ $post_type ][ $id ] ) ? $this->data[ $object_type ][ $post_type ][ $id ] : false;
	}
}
