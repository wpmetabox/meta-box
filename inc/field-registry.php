<?php
/**
 * A registry for storing all fields.
 *
 * @link https://designpatternsphp.readthedocs.io/en/latest/Structural/Registry/README.html
 */
class RWMB_Field_Registry {
	private $data = [];

	private $meta_box;

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

		// $this->register_meta( $field, $type, $object_type );

		do_action( 'rwmb_field_registered', $field, $type, $object_type );
	}

	public function set_meta_box( array $meta_box ) {
		$this->meta_box = $meta_box;
	}

	public function get_meta_box() {
		return $this->meta_box;
	}

	public function register_meta( $field, $type, $object_type ) {
		// Bail early if the field is implicitly not registered as meta.
		if ( ! $field['register_meta'] ) {
			return;
		}

		// Bail if the meta box storage is custom table.
		if ( ! isset( $field['storage'] ) || ! $field['storage'] instanceof RWMB_Post_Storage ) {
			return;
		}

		$type_default = $this->get_return_type_and_default( $field );

		$args = [
			'object_subtype'   => $object_type,
			'type'             =>  $type_default['type'],
			'description'      => $field['desc'] ?? '',
			'single'           => ! $field['multiple'],
			'default'          => $type_default['default'],
			'show_in_rest'     => true,
			'revision_enabled' => ( isset( $this->meta_box['revision'] ) && $this->meta_box['revision'] && $object_type === 'post' ) || false,
		];

		$field_register_meta = is_array( $field['register_meta'] ) ? $field['register_meta'] : [];

		// Merge the args with the field's args.
		$args = array_merge( $args, $field_register_meta );

		register_meta( $object_type, $field['id'], $args );
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
