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
		if ( null !== wp_get_ability_category( 'meta-box' ) ) {
			return;
		}

		wp_register_ability_category( 'meta-box', [
			'label'       => __( 'Meta Box', 'meta-box' ),
			'description' => __( 'Get, update, and delete custom field values created by the Meta Box plugin.', 'meta-box' ),
		] );
	}

	/**
	 * Register the three field-value abilities.
	 * Update covers create too: WP update_metadata() auto-adds the row when absent.
	 */
	public function register_abilities(): void {
		$input_schema = $this->get_input_schema();

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
			'input_schema'        => $input_schema,
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
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The object ID (post, term, or user).', 'meta-box' ),
				],
				'object_type' => [
					'type'        => 'string',
					'enum'        => [ 'post', 'term', 'user', 'setting', 'comment' ],
					'default'     => 'post',
					'description' => __( 'The object type the field belongs to.', 'meta-box' ),
				],
				'value'       => [
					'description' => __( 'The value to store. Required for update; ignored for get and delete. Format depends on field type: text/textarea/wysiwyg = string; number/range = integer or float; checkbox/switch = "1" or "0"; select/radio = option value string; select multiple/checkbox-list = array of option values; image/file/media = array of attachment IDs (e.g. [1,2,3]) or comma-separated string; single-image/single-file = single attachment ID; post = post ID or array of IDs; user = user ID or array of IDs; taxonomy = term ID or array of IDs; taxonomy-advanced = comma-separated term IDs; link = array with url/title/target keys; map/osm = "latitude,longitude" or "latitude,longitude,zoom"; datetime/date/time = date string matching field format or Unix timestamp when timestamp=true; color = hex string (e.g. "#ff0000"); background = array with color/image/repeat/position/size keys.', 'meta-box' ),
				],
			],
			'required'             => [ 'field_id', 'object_id' ],
			'additionalProperties' => false,
		];
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
		$object_id   = (int) ( $input['object_id'] ?? 0 );
		$field_id    = isset( $input['field_id'] ) ? (string) $input['field_id'] : '';
		$object_type = isset( $input['object_type'] ) ? (string) $input['object_type'] : 'post';

		if ( ! $object_id || '' === $field_id ) {
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

		if ( $field ) {
			$value = $this->normalize_value( $value, $field );
		}

		rwmb_set_meta( $input['object_id'], $input['field_id'], $value, $args );

		return [ 'success' => true ];
	}

	public function delete_field_value( array $input ): array {
		$args     = array_diff_key( $input, array_flip( [ 'field_id', 'object_id' ] ) );
		$deleted  = rwmb_delete_meta( $input['object_id'], $input['field_id'], $args );

		return [ 'success' => (bool) $deleted ];
	}

	/**
	 * Normalize value based on field type.
	 *
	 * Ensures attachment/object ID fields receive proper array format.
	 * Handles cloneable fields where value is array of arrays.
	 *
	 * @param mixed  $value Raw value from input.
	 * @param array  $field Field settings.
	 * @return mixed Normalized value.
	 */
	private function normalize_value( $value, array $field ) {
		$type   = $field['type'] ?? '';
		$clone  = $field['clone'] ?? false;

		$attachment_fields = [ 'media', 'file', 'image', 'image_advanced', 'file_upload', 'image_upload', 'video' ];
		$object_id_fields  = [ 'post', 'user', 'taxonomy' ];
		$needs_ids         = in_array( $type, array_merge( $attachment_fields, $object_id_fields ), true );

		if ( ! $needs_ids ) {
			return $value;
		}

		if ( $clone && is_array( $value ) ) {
			return array_map( 'wp_parse_id_list', $value );
		}

		return wp_parse_id_list( $value );
	}
}
