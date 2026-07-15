<?php
use PHPUnit\Framework\TestCase;

class FieldReturnTypeTest extends TestCase {
	/**
	 * get_schema() types for the default (non-clone) field.
	 * These describe the field value shape; get_full_schema() adjusts for storage.
	 *
	 * @var array
	 */
	protected $field_return_types = [
		'autocomplete'    => 'array',
		'background'      => 'object',
		'button'          => 'null',
		'button_group'    => 'string',
		'checkbox_list'   => 'array',
		'checkbox'        => 'integer',
		'color'           => 'string',
		'custom_html'     => 'null',
		'date'            => 'string',
		'datetime'        => 'string',
		'divider'         => 'null',
		'file_advanced'   => 'array',
		'file_input'      => 'string',
		'file_upload'     => 'array',
		'file'            => 'array',
		'heading'         => 'null',
		'icon'            => 'string',
		'image_advanced'  => 'array',
		'image_select'    => 'string',
		'image_upload'    => 'array',
		'image'           => 'array',
		'key_value'       => 'array',
		'map'             => 'string',
		'number'          => 'string',
		'oembed'          => 'string',
		'osm'             => 'string',
		'post'            => 'integer',
		'radio'           => 'string',
		'range'           => 'string',
		'select_advanced' => 'string',
		'select'          => 'string',
		'sidebar'         => 'string',
		'single_image'    => 'integer',
		'slider'          => 'string',
		'switch'          => 'integer',
		'taxonomy'        => 'null',
		'text_list'       => 'array',
		'textarea'        => 'string',
		'user'            => 'integer',
		'video'           => 'array',
		'wysiwyg'         => 'string',
	];

	private function setupField( $type, $props = [] ) {
		$field = [
			'id'   => $props['id'] ?? $type,
			'type' => $type,
			...$props,
		];

		return RWMB_Field::call( 'normalize', $field );
	}

	public function testFieldShouldMatchReturnType() {
		foreach ( $this->field_return_types as $field_id => $expected_return_type ) {
			$field  = $this->setupField( $field_id );
			$schema = RWMB_Field::call( 'get_schema', $field );
			$type   = $schema['type'] ?? 'null';

			$this->assertEquals( $expected_return_type, $type, "Field $field_id should return $expected_return_type" );
		}
	}

	public function testCloneFieldWrapsSchemaOnlyWhenAggregated() {
		foreach ( $this->field_return_types as $field_id => $single_return_type ) {
			if ( 'null' === $single_return_type ) {
				continue;
			}

			$field = $this->setupField( $field_id, [ 'clone' => true ] );

			if ( ! $field['clone'] ) {
				continue;
			}

			$inner = RWMB_Field::call( 'get_schema', $field );
			$full  = RWMB_Field::call( 'get_full_schema', $field );

			$this->assertEquals( 'array', $full['type'], "Cloned field $field_id should wrap as array" );
			$this->assertEquals( $inner, $full['items'], "Cloned field $field_id items should match get_schema()" );
			$this->assertTrue( RWMB_Field::call( 'is_single_meta', $field ), "Cloned field $field_id should be single meta" );
		}
	}

	public function testCloneAsMultipleDoesNotWrapSchema() {
		$field = $this->setupField( 'text', [
			'clone'             => true,
			'clone_as_multiple' => true,
		] );

		$inner = RWMB_Field::call( 'get_schema', $field );
		$full  = RWMB_Field::call( 'get_full_schema', $field );

		$this->assertEquals( $inner, $full, 'clone_as_multiple should not wrap schema' );
		$this->assertFalse( RWMB_Field::call( 'is_single_meta', $field ) );
	}

	public function testCheckboxListStorageMatrix() {
		// Multi-row: each row is a string (unwrap get_schema array).
		$field = $this->setupField( 'checkbox_list', [
			'options' => [ 'a' => 'A', 'b' => 'B' ],
		] );
		$this->assertFalse( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'string', RWMB_Field::call( 'get_full_schema', $field )['type'] );

		// Aggregated clones: one meta value, array of arrays of strings.
		$field = $this->setupField( 'checkbox_list', [
			'options' => [ 'a' => 'A', 'b' => 'B' ],
			'clone'   => true,
		] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );
		$this->assertTrue( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'array', $schema['type'] );
		$this->assertEquals( 'array', $schema['items']['type'] );
		$this->assertEquals( 'string', $schema['items']['items']['type'] );

		// clone_as_multiple: multi-row, each row is array of strings.
		$field = $this->setupField( 'checkbox_list', [
			'options'           => [ 'a' => 'A', 'b' => 'B' ],
			'clone'             => true,
			'clone_as_multiple' => true,
		] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );
		$this->assertFalse( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'array', $schema['type'] );
		$this->assertEquals( 'string', $schema['items']['type'] );
	}

	public function testFileStorageMatrix() {
		$field = $this->setupField( 'file' );
		$this->assertFalse( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'integer', RWMB_Field::call( 'get_full_schema', $field )['type'] );

		$field  = $this->setupField( 'file', [ 'clone' => true ] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );
		$this->assertTrue( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'array', $schema['type'] );
		$this->assertEquals( 'array', $schema['items']['type'] );
		$this->assertEquals( 'integer', $schema['items']['items']['type'] );
	}

	public function testGroupFieldShouldReturnObject() {
		if ( ! class_exists( 'RWMB_Group_Field' ) ) {
			$this->markTestSkipped( 'MB Group is not available.' );
		}

		$field  = $this->setupField( 'group', [
			'fields' => [
				$this->setupField( 'text' ),
				$this->setupField( 'textarea' ),
			],
		] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );

		$this->assertEquals( 'object', $schema['type'], 'Group field should return object' );
	}

	public function testCloneableGroupShouldReturnArrayObject() {
		if ( ! class_exists( 'RWMB_Group_Field' ) ) {
			$this->markTestSkipped( 'MB Group is not available.' );
		}

		$field  = $this->setupField( 'group', [
			'fields' => [
				$this->setupField( 'text' ),
				$this->setupField( 'textarea' ),
			],
			'clone'  => true,
		] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );

		$this->assertEquals( 'array', $schema['type'], 'Cloneable group field should return array' );
		$this->assertEquals( 'object', $schema['items']['type'], 'Cloneable group field should return object' );
	}

	public function testFieldsetTextShouldReturnObject() {
		$field  = $this->setupField( 'fieldset_text', [ 'options' => [ 'name' => 'Name', 'email' => 'Email' ] ] );
		$schema = RWMB_Field::call( 'get_full_schema', $field );

		$this->assertEquals( 'object', $schema['type'], 'Fieldset text field should return object' );
		$this->assertArrayHasKey( 'properties', $schema );
		$this->assertArrayHasKey( 'name', $schema['properties'] );
		$this->assertArrayHasKey( 'email', $schema['properties'] );
		$this->assertEquals( 'string', $schema['properties']['name']['type'] );
		$this->assertEquals( 'string', $schema['properties']['email']['type'] );
	}

	public function testTaxonomyAdvancedField() {
		$field_single = $this->setupField( 'taxonomy_advanced', [ 'taxonomy' => 'category' ] );
		$schema       = RWMB_Field::call( 'get_full_schema', $field_single );

		$this->assertEquals( 'integer', $schema['type'] );
		$this->assertTrue( RWMB_Field::call( 'is_single_meta', $field_single ) );

		$field_multiple = $this->setupField( 'taxonomy_advanced', [
			'taxonomy' => 'category',
			'multiple' => true,
		] );
		$schema_multiple = RWMB_Field::call( 'get_full_schema', $field_multiple );

		$this->assertEquals( 'string', $schema_multiple['type'] );
		$this->assertTrue(
			RWMB_Field::call( 'is_single_meta', $field_multiple ),
			'taxonomy_advanced always stores one CSV meta row'
		);
	}

	public function testPostMultipleUsesItemSchema() {
		$field = $this->setupField( 'post', [
			'post_type' => 'post',
			'multiple'  => true,
		] );

		$this->assertFalse( RWMB_Field::call( 'is_single_meta', $field ) );
		$this->assertEquals( 'integer', RWMB_Field::call( 'get_full_schema', $field )['type'] );
	}
}
