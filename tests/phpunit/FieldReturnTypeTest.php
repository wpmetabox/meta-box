<?php
use PHPUnit\Framework\TestCase;

class FieldReturnTypeTest extends TestCase {
	/**
	 * All meta box fields and their return types.
	 *
	 * Note: group is covered separately when MB Group is available.
	 * fieldset_text is covered in testFieldsetTextShouldReturnObject().
	 *
	 * @var array
	 */
	protected $field_return_types = [
		'autocomplete'   => 'array',
		'background'     => 'object', // Key-value pairs map to object schema.
		'button'         => 'null',
		'button_group'   => 'string',
		'checkbox_list'  => 'array',
		'checkbox'       => 'integer',
		'color'          => 'string',
		'custom_html'    => 'null',
		'date'           => 'string',
		'datetime'       => 'string',
		'divider'        => 'null',
		'file_advanced'  => 'array',
		'file_input'     => 'string',
		'file_upload'    => 'array',
		'file'           => 'array',
		'heading'        => 'null',
		'icon'           => 'string',
		'image_advanced' => 'array',
		'image_select'   => 'string',
		'image_upload'   => 'array',
		'image'          => 'array',
		'key_value'      => 'string',
		'map'            => 'string',
		'oembed'         => 'string',
		'osm'            => 'string',
		'post'           => 'integer',
		'radio'          => 'string',
		'range'          => 'number',
		'select_advanced'=> 'string',
		'select'         => 'string',
		'sidebar'        => 'string',
		'single_image'   => 'integer',
		'switch'         => 'integer',
		'taxonomy'       => 'null', // Uses term relationships, not post meta.
		'text_list'      => 'array',
		'textarea'       => 'string',
		'user'           => 'integer',
		'video'          => 'array',
		'wysiwyg'        => 'string',
	];

	private function setupField( $field_id, $props = [] ) {
		$field = [
			'id'   => $field_id,
			'type' => $field_id,
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

	public function testCloneFieldShouldReturnArray() {
		foreach ( $this->field_return_types as $field_id => $single_return_type ) {
			if ( 'null' === $single_return_type ) {
				continue;
			}

			$field = $this->setupField( $field_id, [ 'clone' => true ] );

			// Some fields force clone off (e.g. taxonomy).
			if ( empty( $field['clone'] ) ) {
				continue;
			}

			$schema = RWMB_Field::call( 'get_full_schema', $field );

			$this->assertEquals( 'array', $schema['type'], "Field $field_id should return array" );
			$this->assertEquals( $single_return_type, $schema['items']['type'], "Item in field $field_id should return $single_return_type" );
		}
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
		$this->assertArrayHasKey( 'properties', $schema, 'Fieldset text field should have properties' );
		$this->assertArrayHasKey( 'name', $schema['properties'], 'Fieldset text field should have name property' );
		$this->assertArrayHasKey( 'email', $schema['properties'], 'Fieldset text field should have email property' );
		$this->assertEquals( 'string', $schema['properties']['name']['type'], 'Fieldset text field name property should return string' );
		$this->assertEquals( 'string', $schema['properties']['email']['type'], 'Fieldset text field email property should return string' );
	}

	public function testTaxonomyAdvancedField() {
		$field_single = $this->setupField( 'taxonomy_advanced', [ 'taxonomy' => 'category' ] );
		$schema       = RWMB_Field::call( 'get_full_schema', $field_single );

		$this->assertEquals( 'integer', $schema['type'], 'Single taxonomy advanced should return integer' );

		$field_multiple  = $this->setupField( 'taxonomy_advanced', [ 'taxonomy' => 'category', 'multiple' => true ] );
		$schema_multiple = RWMB_Field::call( 'get_full_schema', $field_multiple );

		$this->assertEquals( 'string', $schema_multiple['type'], 'Multiple taxonomy advanced should return CSV' );
	}
}
