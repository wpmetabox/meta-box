<?php
namespace MetaBox\Integrations\BlockBindings;

use RWMB_Field;
use WP_Block;

/**
 * Block bindings source for Meta Box fields.
 *
 * Subclasses provide object-specific data. Register via:
 * `rwmb_get_registry( 'block_bindings' )->add( new Post() )`.
 */
abstract class Source {
	protected const VALUE_KEYS = [
		'single_image'      => [ 'url', 'alt', 'title', 'caption', 'description' ],
		'image'             => [ 'url', 'alt', 'title', 'caption', 'description' ],
		'image_advanced'    => [ 'url', 'alt', 'title', 'caption', 'description' ],
		'image_upload'      => [ 'url', 'alt', 'title', 'caption', 'description' ],
		'file'              => [ 'url', 'title' ],
		'file_advanced'     => [ 'url', 'title' ],
		'file_upload'       => [ 'url', 'title' ],
		'background'        => [ 'image' ],
		'link'              => [ 'url', 'title', 'target' ],
		'map'               => [ 'latitude', 'longitude' ],
		'osm'               => [ 'latitude', 'longitude' ],
		'post'              => [ 'post_title', 'post_excerpt', 'post_content', 'post_date', 'post_modified', 'post_author', 'url' ],
		'taxonomy'          => [ 'name', 'slug', 'description', 'url' ],
		'taxonomy_advanced' => [ 'name', 'slug', 'description', 'url' ],
		'user'              => [ 'display_name', 'user_url' ],
		'video'             => [ 'src', 'title', 'caption', 'description' ],
	];

	abstract protected function name(): string;

	abstract protected function label(): string;

	/**
	 * Block context keys this source needs (e.g. postId, postType).
	 *
	 * @return string[]
	 */
	abstract protected function contexts(): array;

	/**
	 * Meta Box object type: post, term, user, or setting.
	 */
	abstract protected function object_type(): string;

	/**
	 * Context property used to pick the fields list in the editor.
	 * Empty string = return the fields list as-is (flat or single group).
	 */
	abstract protected function context_key(): string;

	/**
	 * @param array    $source_args    Binding args (field id, optional key, …).
	 * @param WP_Block $block_instance Block instance.
	 * @return int|string|null Object ID for value resolution.
	 */
	abstract protected function get_object_id( array $source_args, WP_Block $block_instance );

	/**
	 * Registry / field-settings "type" (post type, taxonomy, option name, …).
	 *
	 * @param array    $source_args    Binding args.
	 * @param WP_Block $block_instance Block instance.
	 */
	abstract protected function get_type( array $source_args, WP_Block $block_instance ): string;

	/**
	 * Whether the current request may access fields for this object.
	 *
	 * @param int|string $object_id Object ID.
	 */
	abstract protected function has_permission( $object_id, WP_Block $block_instance ): bool;

	/**
	 * Fields for the bindings UI.
	 *
	 * Usually keyed by context value (post type, taxonomy, …).
	 * Settings sources may return a flat list.
	 *
	 * @return array
	 */
	abstract protected function get_fields(): array;

	public function register(): void {
		register_block_bindings_source( $this->name(), [
			'label'              => $this->label(),
			'get_value_callback' => [ $this, 'get_value' ],
			'uses_context'       => $this->contexts(),
		] );
	}

	/**
	 * Editor config for the bindings UI.
	 *
	 * @return array{name: string, label: string, usesContext: string[], contextKey: string, fields: array}
	 */
	public function get_editor_data(): array {
		return [
			'name'        => $this->name(),
			'label'       => $this->label(),
			'usesContext' => $this->contexts(),
			'contextKey'  => $this->context_key(),
			'fields'      => $this->get_fields(),
		];
	}

	/**
	 * Resolve the field value for a bound block attribute (front-end render).
	 *
	 * @param array    $source_args    Field id and optional value key.
	 * @param WP_Block $block_instance Block instance.
	 * @param string   $attribute_name Bound block attribute.
	 * @return mixed
	 */
	public function get_value( array $source_args, WP_Block $block_instance, string $attribute_name ) {
		$field_id  = $source_args['id'] ?? '';
		$object_id = $this->get_object_id( $source_args, $block_instance );
		if ( ! $field_id || ! $object_id ) {
			return null;
		}

		if ( ! $this->has_permission( $object_id, $block_instance ) ) {
			return null;
		}

		$args  = [
			'object_type' => $this->object_type(),
			'type'        => $this->get_type( $source_args, $block_instance ),
		];
		$field = rwmb_get_field_settings( $field_id, $args, $object_id );
		if ( ! $field || $field['hide_from_block_bindings'] ) {
			return null;
		}

		// Reuse the already-fetched $field instead of rwmb_get_value(), which would look it up again.
		$value = $this->get_single_value( RWMB_Field::call( 'get_value', $field, $args, $object_id ), $field );
		if ( $this->is_empty( $value ) ) {
			return null;
		}

		// `key` picks a part of a structured value, falling back to the bound attribute name (e.g. Image block `url`).
		return $this->format_value( $value, $field, $source_args['key'] ?? $attribute_name );
	}

	/**
	 * Binding options for one field (label + type + args).
	 *
	 * @return list<array{label: string, type: string, args: array{id: string, key?: string}}>
	 */
	protected function binding_options( array $field ): array {
		$name = $field['name'] ?: $field['id'];
		$id   = $field['id'];

		// Keys come from the field's options config.
		if ( 'fieldset_text' === $field['type'] && ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
			return $this->value_key_options( $name, $id, array_keys( $field['options'] ), $field['options'] );
		}

		if ( isset( self::VALUE_KEYS[ $field['type'] ] ) ) {
			return $this->value_key_options( $name, $id, self::VALUE_KEYS[ $field['type'] ] );
		}

		return [
			[
				'label' => $name,
				'type'  => 'string',
				'args'  => [ 'id' => $id ],
			],
		];
	}

	/**
	 * @param string   $name   Field label.
	 * @param string   $id     Field id.
	 * @param string[] $keys   Value keys to expose (url, alt, caption, …).
	 * @param string[] $labels Optional labels keyed by value key.
	 * @return list<array{label: string, type: string, args: array{id: string, key: string}}>
	 */
	protected function value_key_options( string $name, string $id, array $keys, array $labels = [] ): array {
		$options = [];
		foreach ( $keys as $key ) {
			$label     = $labels[ $key ] ?? $this->value_key_label( $key );
			$options[] = [
				// translators: 1: field name, 2: value property (URL, Caption, …).
				'label' => sprintf( __( '%1$s: %2$s', 'meta-box' ), $name, wp_strip_all_tags( $label ) ),
				'type'  => 'string',
				'args'  => [
					'id'  => $id,
					'key' => $key,
				],
			];
		}

		return $options;
	}

	protected function value_key_label( string $key ): string {
		$labels = [
			'url'           => __( 'URL', 'meta-box' ),
			'alt'           => __( 'Alt Text', 'meta-box' ),
			'title'         => __( 'Title', 'meta-box' ),
			'caption'       => __( 'Caption', 'meta-box' ),
			'description'   => __( 'Description', 'meta-box' ),
			'name'          => __( 'Name', 'meta-box' ),
			'src'           => __( 'Source URL', 'meta-box' ),
			'latitude'      => __( 'Latitude', 'meta-box' ),
			'longitude'     => __( 'Longitude', 'meta-box' ),
			'image'         => __( 'Image', 'meta-box' ),
			'target'        => __( 'Target', 'meta-box' ),
			'post_title'    => __( 'Title', 'meta-box' ),
			'post_excerpt'  => __( 'Excerpt', 'meta-box' ),
			'post_content'  => __( 'Content', 'meta-box' ),
			'post_date'     => __( 'Published Date', 'meta-box' ),
			'post_modified' => __( 'Modified Date', 'meta-box' ),
			'post_author'   => __( 'Author', 'meta-box' ),
			'slug'          => __( 'Slug', 'meta-box' ),
			'display_name'  => __( 'Display Name', 'meta-box' ),
			'user_url'      => __( 'URL', 'meta-box' ),
		];

		return $labels[ $key ] ?? $key;
	}

	/**
	 * Reduce clone / multiple values to a single unit value.
	 *
	 * @param mixed $value Value from RWMB_Field::get_value().
	 * @param array $field Field settings.
	 * @return mixed
	 */
	protected function get_single_value( $value, array $field ) {
		foreach ( [ 'clone', 'multiple' ] as $prop ) {
			if ( ! $field[ $prop ] ) {
				continue;
			}
			$value = is_array( $value ) && $value ? reset( $value ) : null;
			if ( $this->is_empty( $value ) ) {
				return null;
			}
		}

		return $value;
	}

	/**
	 * Map a single field value to the requested key / block attribute.
	 *
	 * @param mixed  $value Single field value.
	 * @param array  $field Field settings.
	 * @param string $key   Value key or block attribute name.
	 * @return mixed
	 */
	protected function format_value( $value, array $field, string $key ) {
		if ( 'post' === $field['type'] ) {
			$post = get_post( $value );
			if ( ! $post ) {
				return null;
			}
			if ( 'url' === $key ) {
				return get_permalink( $post );
			}
			if ( 'post_author' === $key ) {
				return get_the_author_meta( 'display_name', $post->post_author );
			}
			$value = $post;
		}

		if ( 'user' === $field['type'] ) {
			$value = get_userdata( $value );
			if ( ! $value ) {
				return null;
			}
		}

		if ( in_array( $field['type'], [ 'taxonomy', 'taxonomy_advanced' ], true ) && 'url' === $key ) {
			$url = get_term_link( $value );
			return is_wp_error( $url ) ? null : $url;
		}

		if ( is_object( $value ) ) {
			return $this->to_string( $value->$key ?? null );
		}

		if ( is_array( $value ) ) {
			// Image uses full_url/url; video uses src.
			if ( in_array( $key, [ 'url', 'href' ], true ) ) {
				return $this->to_string( $value['full_url'] ?? $value['url'] ?? $value['src'] ?? null );
			}
			return $this->to_string( $value[ $key ] ?? null );
		}

		return $this->to_string( $value );
	}

	/**
	 * @param mixed $value
	 */
	protected function is_empty( $value ): bool {
		return null === $value || '' === $value || false === $value;
	}

	/**
	 * @param mixed $value
	 */
	protected function to_string( $value ): ?string {
		return is_scalar( $value ) ? (string) $value : null;
	}
}
