<?php
/**
 * A registry for storing all fields.
 *
 * @link https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 */
class RWMB_Field_Registry {
	private $data = [];

	/**
	 * Add a single field to the registry.
	 *
	 * @param array  $field       Field configuration.
	 * @param string $type        Post type|Taxonomy|'user'|Setting page which the field belongs to.
	 * @param string $object_type Object type which the field belongs to.
	 */
	public function add( array $field, string $type, string $object_type = 'post' ) {
		if ( ! isset( $field['id'] ) ) {
			return;
		}

		if ( empty( $this->data[ $object_type ] ) ) {
			$this->data[ $object_type ] = [];
		}
		if ( empty( $this->data[ $object_type ][ $type ] ) ) {
			$this->data[ $object_type ][ $type ] = [];
		}
		$this->data[ $object_type ][ $type ][ $field['id'] ] = $field;

		do_action( 'rwmb_field_registered', $field, $type, $object_type );
	}

	/**
	 * Retrieve a field.
	 *
	 * @param string $id          A meta box instance id.
	 * @param string $type        Post type|Taxonomy|'user'|Setting page which the field belongs to.
	 * @param string $object_type Object type which the field belongs to.
	 *
	 * @return bool|array False or field configuration.
	 */
	public function get( $id, $type, $object_type = 'post' ) {
		return $this->data[ $object_type ][ $type ][ $id ] ?? false;
	}

	/**
	 * Retrieve fields by object type.
	 *
	 * @param string $object_type Object type which the field belongs to.
	 *
	 * @return array List of fields.
	 */
	public function get_by_object_type( string $object_type = 'post' ) : array {
		return $this->data[ $object_type ] ?? [];
	}
}
