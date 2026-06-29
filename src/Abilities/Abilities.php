<?php
namespace MetaBox\Abilities;

/**
 * Registers Meta Box custom-field CRUD operations as WordPress abilities.
 */
class Abilities {
	public function init(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Register the meta-box category once.
	 * Guarded against double registration (e.g. extensions or repeated init).
	 */
	public function register_category(): void {
		if ( wp_has_ability_category( 'meta-box' ) ) {
			return;
		}

		wp_register_ability_category( 'meta-box', [
			'label'       => __( 'Meta Box', 'meta-box' ),
			'description' => __( 'Abilities for Meta Box data (post types, taxonomies, fields, etc.).', 'meta-box' ),
		] );
	}

	public function register_abilities(): void {
		$input_schema = $this->get_input_schema();

		wp_register_ability( 'meta-box/get-field-value-format', [
			'category'            => 'meta-box',
			'label'               => __( 'Get expected value format for a field', 'meta-box' ),
			'description'         => __( 'Return expected value format and example for a Meta Box field. Provide field_type for type-level info, or field_id + object_id for field-level info that accounts for multiple/clone settings.', 'meta-box' ),
			'input_schema'        => [
				'type'                 => 'object',
				'properties'           => [
					'field_type'  => [
						'type'        => 'string',
						'description' => __( 'Meta Box field type slug. Alternative to field_id.', 'meta-box' ),
					],
					'field_id'    => [
						'type'        => 'string',
						'description' => __( 'Field meta key. When given, resolves field settings (multiple/clone) for precise format.', 'meta-box' ),
					],
					'object_id'   => [
						'type'        => [ 'integer', 'string' ],
						'description' => __( 'Object ID (post, term, user ID, or option name). Required when field_id is given.', 'meta-box' ),
					],
					'object_type' => [
						'type'        => 'string',
						'enum'        => [ 'post', 'term', 'user', 'setting', 'comment' ],
						'default'     => 'post',
						'description' => __( 'Object type. Used with field_id.', 'meta-box' ),
					],
				],
				'additionalProperties' => false,
			],
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'field_type'      => [
						'type'        => 'string',
						'description' => __( 'The field type.', 'meta-box' ),
					],
					'format'          => [
						'type'        => 'string',
						'description' => __( 'Human-readable description of expected value format.', 'meta-box' ),
					],
					'example'         => [
						'description' => __( 'Example of a valid value.', 'meta-box' ),
					],
					'is_array'        => [
						'type'        => 'boolean',
						'description' => __( 'Whether value is an array.', 'meta-box' ),
					],
					'is_multiple'     => [
						'type'        => 'boolean',
						'description' => __( 'Whether field has multiple=true (allows selecting multiple values).', 'meta-box' ),
					],
					'is_cloneable'    => [
						'type'        => 'boolean',
						'description' => __( 'Whether field has clone=true (stores array of values).', 'meta-box' ),
					],
					'value_structure' => [
						'type'        => 'string',
						'enum'        => [ 'scalar', 'array', 'array_of_arrays' ],
						'description' => __( 'Overall value shape. scalar = single value; array = array of values (multiple or clone); array_of_arrays = nested array (multiple + clone).', 'meta-box' ),
					],
				],
				'additionalProperties' => false,
			],
			'meta'                => [
				'annotations' => [
					'readonly'      => true,
					'destructive'   => false,
					'openWorldHint' => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
			'permission_callback' => function ( $input ) {
				$field_id    = $input['field_id'] ?? '';
				$object_id   = $input['object_id'] ?? 0;
				$object_type = $input['object_type'] ?? 'post';

				// Field-level: require edit capability on the object.
				if ( $field_id && $object_id ) {
					$cap = $this->map_capability( $object_type, 'update' );
					return current_user_can( $cap, $object_id );
				}

				// Type-level: light read gate.
				return current_user_can( 'read' );
			},
			'execute_callback'    => [ $this, 'get_field_value_format' ],
		] );

		wp_register_ability( 'meta-box/get-field-value', [
			'category'            => 'meta-box',
			'label'               => __( 'Get a custom field value', 'meta-box' ),
			'description'         => __( 'Retrieve the stored value of a Meta Box custom field for a given object.', 'meta-box' ),
			'input_schema'        => $input_schema,
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'value' => [
						'description' => __( 'The stored field value.', 'meta-box' ),
					],
				],
				'additionalProperties' => false,
			],
			'meta'                => [
				'annotations' => [
					'readonly'      => true,
					'destructive'   => false,
					'openWorldHint' => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
			'permission_callback' => function ( $input ) {
				return $this->check_permission( $input, 'get' );
			},
			'execute_callback'    => [ $this, 'get_field_value' ],
		] );

		wp_register_ability( 'meta-box/update-field-value', [
			'category'            => 'meta-box',
			'label'               => __( 'Update (or create) a custom field value', 'meta-box' ),
			'description'         => __( 'Set the value of a Meta Box custom field. Creates the field value if it does not exist yet.', 'meta-box' ),
			'input_schema'        => $this->get_update_input_schema(),
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'success' => [
						'type'        => 'boolean',
						'description' => __( 'Whether the value was saved.', 'meta-box' ),
					],
				],
				'additionalProperties' => false,
			],
			'meta'                => [
				'annotations' => [
					'readonly'      => false,
					'destructive'   => false,
					'idempotent'    => true,
					'openWorldHint' => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
			'permission_callback' => function ( $input ) {
				return $this->check_permission( $input, 'update' );
			},
			'execute_callback'    => [ $this, 'update_field_value' ],
		] );

		wp_register_ability( 'meta-box/delete-field-value', [
			'category'            => 'meta-box',
			'label'               => __( 'Delete a custom field value', 'meta-box' ),
			'description'         => __( 'Remove the stored value of a Meta Box custom field for a given object.', 'meta-box' ),
			'input_schema'        => $input_schema,
			'output_schema'       => [
				'type'                 => 'object',
				'properties'           => [
					'success' => [
						'type'        => 'boolean',
						'description' => __( 'Whether the value was deleted.', 'meta-box' ),
					],
				],
				'additionalProperties' => false,
			],
			'meta'                => [
				'annotations' => [
					'readonly'      => false,
					'destructive'   => true,
					'openWorldHint' => false,
				],
				'mcp'         => [
					'public' => true,
					'type'   => 'tool',
				],
			],
			'permission_callback' => function ( $input ) {
				return $this->check_permission( $input, 'delete' );
			},
			'execute_callback'    => [ $this, 'delete_field_value' ],
		] );
	}

	/**
	 * Shared input schema for the three abilities.
	 * `value` is only meaningful for update; get/delete ignore it.
	 */
	private function get_input_schema(): array {
		return [
			'type'                 => 'object',
			'properties'           => [
				'field_id'    => [
					'type'        => 'string',
					'description' => __( 'The custom field meta key.', 'meta-box' ),
				],
				'object_id'   => [
					'type'        => [ 'integer', 'string' ],
					'description' => __( 'The object ID (post, term, user ID, or settings page slug).', 'meta-box' ),
				],
				'object_type' => [
					'type'        => 'string',
					'enum'        => [ 'post', 'term', 'user', 'setting', 'comment' ],
					'default'     => 'post',
					'description' => __( 'The object type the field belongs to.', 'meta-box' ),
				],
				'value'       => [
					'description' => __( 'The value to store. Required for update; ignored for get and delete. Use meta-box/get-field-value-format ability to get expected format for a specific field type.', 'meta-box' ),
				],
			],
			'required'             => [ 'field_id', 'object_id' ],
			'additionalProperties' => false,
		];
	}

	/**
	 * Input schema for the update ability — same as shared schema but requires `value`.
	 */
	private function get_update_input_schema(): array {
		$schema               = $this->get_input_schema();
		$schema['required'][] = 'value';

		return $schema;
	}

	/**
	 * Permission gate shared by the three abilities.
	 *
	 * - Field must be registered (blocks arbitrary meta key access).
	 * - Current user must hold the object-level capability for the verb.
	 *
	 * @param array  $input Ability input.
	 * @param string $verb  get|update|delete.
	 * @return bool
	 */
	public function check_permission( array $input, string $verb ): bool {
		$object_id   = $input['object_id'] ?? 0;
		$field_id    = $input['field_id'] ?? '';
		$object_type = $input['object_type'] ?? 'post';

		if ( ! $field_id || ! $object_id ) {
			return false;
		}

		// Only allow access to fields Meta Box knows about.
		$field = rwmb_get_field_settings( $field_id, [ 'object_type' => $object_type ], $object_id );
		if ( false === $field ) {
			return false;
		}

		$cap = $this->map_capability( $object_type, $verb );

		return current_user_can( $cap, $object_id );
	}

	/**
	 * Map object type + verb to a WordPress capability.
	 *
	 * @param string $object_type post|term|user|setting.
	 * @param string $verb        get|update|delete.
	 * @return string WordPress capability name.
	 */
	private function map_capability( string $object_type, string $verb ): string {
		$is_read = 'get' === $verb;

		switch ( $object_type ) {
			case 'term':
				return $is_read ? 'assign_term' : 'edit_term';
			case 'user':
				return $is_read ? 'read' : 'edit_user';
			case 'setting':
				return $is_read ? 'read' : 'manage_options';
			case 'comment':
				return $is_read ? 'read' : 'moderate_comments';
			case 'post':
			default:
				return $is_read ? 'read_post' : 'edit_post';
		}
	}

	public function get_field_value( array $input ): array {
		$args  = array_diff_key( $input, array_flip( [ 'field_id', 'object_id' ] ) );
		$value = rwmb_get_value( $input['field_id'], $args, $input['object_id'] );

		return [ 'value' => $value ];
	}

	public function update_field_value( array $input ): array {
		$args  = array_diff_key( $input, array_flip( [ 'field_id', 'object_id', 'value' ] ) );
		$value = $input['value'] ?? null;

		$field = rwmb_get_field_settings( $input['field_id'], $args, $input['object_id'] );

		if ( ! $field ) {
			return [ 'success' => false ];
		}

		$value = $this->normalize_value( $value, $field );
		rwmb_set_meta( $input['object_id'], $input['field_id'], $value, $args );

		return [ 'success' => true ];
	}

	public function delete_field_value( array $input ): array {
		$args    = array_diff_key( $input, array_flip( [ 'field_id', 'object_id' ] ) );
		$deleted = rwmb_delete_meta( $input['object_id'], $input['field_id'], $args );

		return [ 'success' => (bool) $deleted ];
	}

	public function get_field_value_format( array $input ): array {
		$field_id    = $input['field_id'] ?? '';
		$field_type  = $input['field_type'] ?? '';
		$object_id   = $input['object_id'] ?? 0;
		$object_type = $input['object_type'] ?? 'post';

		// Field-level mode: resolve settings from a real field.
		if ( $field_id && $object_id ) {
			$field = rwmb_get_field_settings( $field_id, [ 'object_type' => $object_type ], $object_id );
			if ( false === $field ) {
				return [
					'field_id'        => $field_id,
					'field_type'      => '',
					'format'          => __( 'Field not found.', 'meta-box' ),
					'example'         => null,
					'is_array'        => false,
					'is_multiple'     => false,
					'is_cloneable'    => false,
					'value_structure' => 'scalar',
				];
			}

			$field_type = $field['type'] ?? '';
			$multiple   = ! empty( $field['multiple'] );
			$clone      = ! empty( $field['clone'] );

			$types = $this->get_value_formats();
			if ( ! isset( $types[ $field_type ] ) ) {
				return [
					'field_id'        => $field_id,
					'field_type'      => $field_type,
					'format'          => __( 'Unknown field type.', 'meta-box' ),
					'example'         => null,
					'is_array'        => false,
					'is_multiple'     => $multiple,
					'is_cloneable'    => $clone,
					'value_structure' => ( $multiple && $clone ) ? 'array_of_arrays' : ( $multiple || $clone ? 'array' : 'scalar' ),
				];
			}

			$format                 = $types[ $field_type ];
			$format                 = $this->apply_field_settings( $format, $multiple, $clone );
			$format['field_id']     = $field_id;
			$format['field_type']   = $field_type;
			$format['is_multiple']  = $multiple;
			$format['is_cloneable'] = $clone;

			return $format;
		}

		// Type-level mode: generic format without field settings.
		$types = $this->get_value_formats();
		if ( ! isset( $types[ $field_type ] ) ) {
			return [
				'field_type'      => $field_type,
				'format'          => __( 'Unknown field type.', 'meta-box' ),
				'example'         => null,
				'is_array'        => false,
				'is_multiple'     => false,
				'is_cloneable'    => false,
				'value_structure' => 'scalar',
			];
		}

		$format                    = $types[ $field_type ];
		$format['field_type']      = $field_type;
		$format['is_multiple']     = false;
		$format['is_cloneable']    = false;
		$format['value_structure'] = $format['is_array'] ? 'array' : 'scalar';

		return $format;
	}

	/**
	 * Modify format info based on multiple/clone settings.
	 *
	 * Wraps example to reflect actual value shape. Sets value_structure
	 * to scalar / array / array_of_arrays based on is_array + flags.
	 *
	 * @param array{format: string, example: mixed, is_array: bool} $format   Base format from type map.
	 * @param bool                                                  $multiple Whether field is multiple.
	 * @param bool                                                  $clone    Whether field is cloneable.
	 * @return array Adjusted format.
	 */
	private function apply_field_settings( array $format, bool $multiple, bool $clone ): array {
		$base_is_array = $format['is_array'];
		$example       = $format['example'];

		if ( $multiple && $clone ) {
			$format['example']         = [ [ $example ] ];
			$format['is_array']        = true;
			$format['value_structure'] = 'array_of_arrays';
		} elseif ( $multiple || $clone ) {
			$format['example']         = [ $example ];
			$format['is_array']        = true;
			$format['value_structure'] = 'array';
		} else {
			$format['value_structure'] = $base_is_array ? 'array' : 'scalar';
		}

		return $format;
	}

	/**
	 * Map of field types → value format info.
	 *
	 * @return array<string, array{format: string, example: mixed, is_array: bool}>
	 */
	private function get_value_formats(): array {
		return [
			'autocomplete'      => [
				'format'   => __( 'String (term slug or ID)', 'meta-box' ),
				'example'  => 'term-slug',
				'is_array' => false,
			],
			'background'        => [
				'format'   => __( 'Array with color, image, repeat, position, size keys', 'meta-box' ),
				'example'  => [
					'color'    => '#ff0000',
					'image'    => '',
					'repeat'   => 'repeat',
					'position' => 'top left',
					'size'     => 'auto',
				],
				'is_array' => true,
			],
			'button_group'      => [
				'format'   => __( 'String (selected option value)', 'meta-box' ),
				'example'  => 'option_key',
				'is_array' => false,
			],
			'checkbox'          => [
				'format'   => __( 'String "1" or "0"', 'meta-box' ),
				'example'  => '1',
				'is_array' => false,
			],
			'checkbox_list'     => [
				'format'   => __( 'Array of strings (selected option values)', 'meta-box' ),
				'example'  => [ 'option_1', 'option_2' ],
				'is_array' => true,
			],
			'color'             => [
				'format'   => __( 'String (hex color)', 'meta-box' ),
				'example'  => '#ff0000',
				'is_array' => false,
			],
			'date'              => [
				'format'   => __( 'String (date format) or integer (Unix timestamp when timestamp=true)', 'meta-box' ),
				'example'  => '2024-01-15',
				'is_array' => false,
			],
			'datetime'          => [
				'format'   => __( 'String (datetime format) or integer (Unix timestamp when timestamp=true)', 'meta-box' ),
				'example'  => '2024-01-15 10:30:00',
				'is_array' => false,
			],
			'email'             => [
				'format'   => __( 'String (email)', 'meta-box' ),
				'example'  => 'user@example.com',
				'is_array' => false,
			],
			'fieldset_text'     => [
				'format'   => __( 'Array with sub-field keys', 'meta-box' ),
				'example'  => [
					'name' => 'John',
					'age'  => 30,
				],
				'is_array' => true,
			],
			'file'              => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'file_input'        => [
				'format'   => __( 'String (file URL)', 'meta-box' ),
				'example'  => 'https://example.com/file.pdf',
				'is_array' => false,
			],
			'file_upload'       => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'group'             => [
				'format'   => __( 'Associative array of sub-field values', 'meta-box' ),
				'example'  => [
					'sub_field_1' => 'value',
					'sub_field_2' => 42,
				],
				'is_array' => true,
			],
			'icon'              => [
				'format'   => __( 'String (icon class)', 'meta-box' ),
				'example'  => 'dashicons-admin-post',
				'is_array' => false,
			],
			'image'             => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'image_advanced'    => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'image_select'      => [
				'format'   => __( 'String (selected option value)', 'meta-box' ),
				'example'  => 'option_key',
				'is_array' => false,
			],
			'image_upload'      => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'input_list'        => [
				'format'   => __( 'Array of strings', 'meta-box' ),
				'example'  => [ 'value_1', 'value_2' ],
				'is_array' => true,
			],
			'key_value'         => [
				'format'   => __( 'Array of {key, value} objects', 'meta-box' ),
				'example'  => [
					[
						'key'   => 'k1',
						'value' => 'v1',
					],
					[
						'key'   => 'k2',
						'value' => 'v2',
					],
				],
				'is_array' => true,
			],
			'link'              => [
				'format'   => __( 'Array with url, title, target keys', 'meta-box' ),
				'example'  => [
					'url'    => 'https://example.com',
					'title'  => 'Example',
					'target' => '_blank',
				],
				'is_array' => true,
			],
			'map'               => [
				'format'   => __( 'String "latitude,longitude" or "latitude,longitude,zoom"', 'meta-box' ),
				'example'  => '40.7128,-74.0060,10',
				'is_array' => false,
			],
			'media'             => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'number'            => [
				'format'   => __( 'Integer or float', 'meta-box' ),
				'example'  => 42,
				'is_array' => false,
			],
			'oembed'            => [
				'format'   => __( 'String (URL)', 'meta-box' ),
				'example'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
				'is_array' => false,
			],
			'password'          => [
				'format'   => __( 'String', 'meta-box' ),
				'example'  => 's3cret',
				'is_array' => false,
			],
			'post'              => [
				'format'   => __( 'Integer (post ID)', 'meta-box' ),
				'example'  => 1,
				'is_array' => false,
			],
			'radio'             => [
				'format'   => __( 'String (selected option value)', 'meta-box' ),
				'example'  => 'option_key',
				'is_array' => false,
			],
			'range'             => [
				'format'   => __( 'Integer', 'meta-box' ),
				'example'  => 50,
				'is_array' => false,
			],
			'select'            => [
				'format'   => __( 'String (selected option value)', 'meta-box' ),
				'example'  => 'option_key',
				'is_array' => false,
			],
			'select_advanced'   => [
				'format'   => __( 'String (selected option value)', 'meta-box' ),
				'example'  => 'option_1',
				'is_array' => false,
			],
			'select_tree'       => [
				'format'   => __( 'Array of strings (selected option values)', 'meta-box' ),
				'example'  => [ 'option_1', 'option_2' ],
				'is_array' => true,
			],
			'sidebar'           => [
				'format'   => __( 'String (sidebar ID)', 'meta-box' ),
				'example'  => 'sidebar-1',
				'is_array' => false,
			],
			'single_image'      => [
				'format'   => __( 'Integer (attachment ID)', 'meta-box' ),
				'example'  => 42,
				'is_array' => false,
			],
			'slider'            => [
				'format'   => __( 'Integer', 'meta-box' ),
				'example'  => 75,
				'is_array' => false,
			],
			'switch'            => [
				'format'   => __( 'String "1" or "0"', 'meta-box' ),
				'example'  => '0',
				'is_array' => false,
			],
			'taxonomy'          => [
				'format'   => __( 'Integer (term ID)', 'meta-box' ),
				'example'  => 5,
				'is_array' => false,
			],
			'taxonomy_advanced' => [
				'format'   => __( 'Comma-separated string of term IDs', 'meta-box' ),
				'example'  => '5,6',
				'is_array' => false,
			],
			'text'              => [
				'format'   => __( 'String', 'meta-box' ),
				'example'  => 'Hello World',
				'is_array' => false,
			],
			'text_list'         => [
				'format'   => __( 'Associative array of field values', 'meta-box' ),
				'example'  => [
					'opt_1' => 'Value 1',
					'opt_2' => 'Value 2',
				],
				'is_array' => true,
			],
			'textarea'          => [
				'format'   => __( 'String (multi-line)', 'meta-box' ),
				'example'  => "Line 1\nLine 2",
				'is_array' => false,
			],
			'time'              => [
				'format'   => __( 'String (time format) or integer (Unix timestamp when timestamp=true)', 'meta-box' ),
				'example'  => '10:30:00',
				'is_array' => false,
			],
			'url'               => [
				'format'   => __( 'String (URL)', 'meta-box' ),
				'example'  => 'https://example.com',
				'is_array' => false,
			],
			'user'              => [
				'format'   => __( 'Integer (user ID)', 'meta-box' ),
				'example'  => 1,
				'is_array' => false,
			],
			'video'             => [
				'format'   => __( 'Array of attachment IDs', 'meta-box' ),
				'example'  => [ 42, 57 ],
				'is_array' => true,
			],
			'wysiwyg'           => [
				'format'   => __( 'String (HTML)', 'meta-box' ),
				'example'  => '<p>Hello</p>',
				'is_array' => false,
			],
		];
	}

	/**
	 * Normalize value based on field type.
	 *
	 * Ensures attachment/object ID fields receive proper array format.
	 * Handles cloneable fields where value is array of arrays.
	 *
	 * @param mixed $value Raw value from input.
	 * @param array $field Field settings.
	 * @return mixed Normalized value.
	 */
	private function normalize_value( $value, array $field ) {
		$type     = $field['type'] ?? '';
		$clone    = $field['clone'] ?? false;
		$multiple = $field['multiple'] ?? false;

		$attachment_fields = [ 'media', 'file', 'image', 'image_advanced', 'file_upload', 'image_upload', 'video' ];
		$object_id_fields  = [ 'post', 'user', 'taxonomy' ];
		$needs_ids         = in_array( $type, array_merge( $attachment_fields, $object_id_fields ), true );

		if ( ! $needs_ids ) {
			return $value;
		}

		if ( $clone ) {
			if ( ! is_array( $value ) ) {
				$value = [ $value ];
			}
			if ( $multiple ) {
				return array_map( 'wp_parse_id_list', $value );
			}
			return array_map( 'intval', $value );
		}

		if ( ! $multiple ) {
			return (int) $value;
		}

		return wp_parse_id_list( $value );
	}
}
