<?php
namespace MetaBox\Integrations;

use WP_Block;

/**
 * Register a custom block bindings source: meta-box/field.
 *
 * Fields opt in with `'block_bindings' => true`.
 * The front-end value is resolved in PHP.
 * The editor only needs the fields list to show the source in the block bindings UI.
 */
class BlockBindings {
	public const SOURCE_NAME = 'meta-box/field';

	private const IMAGE_TYPES = [ 'single_image', 'image', 'image_advanced', 'image_upload' ];
	private const FILE_TYPES  = [ 'file', 'file_advanced', 'file_upload' ];
	private const VALUE_KEYS  = [
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

	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue' ] );
	}

	public function register(): void {
		register_block_bindings_source( self::SOURCE_NAME, [
			'label'              => __( 'Meta Box Field', 'meta-box' ),
			'get_value_callback' => [ $this, 'get_value' ],
			'uses_context'       => [ 'postId', 'postType' ],
		] );
	}

	public function enqueue(): void {
		wp_enqueue_script( 'rwmb-block-bindings', RWMB_JS_URL . 'block-bindings.js', [ 'wp-blocks', 'wp-i18n' ], RWMB_VER, true );
		wp_localize_script( 'rwmb-block-bindings', 'rwmbBlockBindings', [
			'fields' => $this->get_editor_fields(),
		] );
	}

	/**
	 * Fields (opted in via `block_bindings`) for the editor UI, keyed by post type.
	 *
	 * Structured fields (image, map, post, user, …) expose selectable value keys
	 * via `args.key`. Scalar fields are listed once as `string`.
	 *
	 * @return array<string, array>
	 */
	private function get_editor_fields(): array {
		$result      = [];
		$post_fields = rwmb_get_registry( 'field' )->get_by_object_type( 'post' );

		foreach ( $post_fields as $post_type => $fields ) {
			foreach ( $fields as $field ) {
				if ( empty( $field['id'] ) || empty( $field['block_bindings'] ) ) {
					continue;
				}

				foreach ( self::binding_options( $field ) as $option ) {
					$result[ $post_type ][] = $option;
				}
			}
		}

		return $result;
	}

	/**
	 * Binding options for one field (label + type + args).
	 *
	 * @return list<array{label: string, type: string, args: array{id: string, key?: string}}>
	 */
	private static function binding_options( array $field ): array {
		$name = $field['name'] ?: $field['id'];
		$id   = $field['id'];

		if ( in_array( $field['type'], self::IMAGE_TYPES, true ) ) {
			return self::value_key_options( $name, $id, [ 'url', 'alt', 'title', 'caption', 'description' ] );
		}

		if ( in_array( $field['type'], self::FILE_TYPES, true ) ) {
			return self::value_key_options( $name, $id, [ 'url', 'title' ] );
		}

		// Keys come from the field's options config (associative array value).
		if ( 'fieldset_text' === $field['type'] && ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
			return self::value_key_options( $name, $id, array_keys( $field['options'] ), $field['options'] );
		}

		if ( isset( self::VALUE_KEYS[ $field['type'] ] ) ) {
			return self::value_key_options( $name, $id, self::VALUE_KEYS[ $field['type'] ] );
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
	 * @param string[] $keys   Value keys to expose (url, alt, id, …).
	 * @param string[] $labels Optional labels keyed by value key.
	 * @return list<array{label: string, type: string, args: array{id: string, key: string}}>
	 */
	private static function value_key_options( string $name, string $id, array $keys, array $labels = [] ): array {
		$options = [];
		foreach ( $keys as $key ) {
			$label = $labels[ $key ] ?? self::value_key_label( $key );
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

	private static function value_key_label( string $key ): string {
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
	 * Resolve the field value for a bound block attribute (front-end render).
	 *
	 * @param array    $source_args    Expects `id` (field id), optional `key` (value property).
	 * @param WP_Block $block_instance Block instance.
	 * @param string   $attribute_name Bound block attribute.
	 * @return mixed
	 */
	public function get_value( array $source_args, WP_Block $block_instance, string $attribute_name ) {
		$field_id = $source_args['id'] ?? '';
		$post_id  = (int) ( $block_instance->context['postId'] ?? 0 );
		if ( ! $field_id || ! $post_id ) {
			return null;
		}

		$post = get_post( $post_id );
		if ( ! $post || post_password_required( $post ) || ( ! is_post_publicly_viewable( $post ) && ! current_user_can( 'read_post', $post_id ) ) ) {
			return null;
		}

		$args  = [
			'object_type' => 'post',
			'type'        => $block_instance->context['postType'] ?? '',
		];
		$field = rwmb_get_field_settings( $field_id, $args, $post_id );
		if ( ! $field || empty( $field['block_bindings'] ) ) {
			return null;
		}

		$value = rwmb_get_value( $field_id, $args, $post_id );
		$value = self::get_single_value( $value, $field );

		if ( null === $value || '' === $value || false === $value ) {
			return null;
		}

		// `key` selects which part of any structured value to use.
		// Falls back to the bound block attribute name (e.g. Image block `url`).
		$key = $source_args['key'] ?? $attribute_name;

		return self::format_value( $value, $field, $key );
	}

	/**
	 * Reduce clone / multiple values to a single unit value.
	 *
	 * @param mixed $value Value from rwmb_get_value().
	 * @param array $field Field settings.
	 * @return mixed
	 */
	private static function get_single_value( $value, array $field ) {
		if ( $field['clone'] ) {
			$value = is_array( $value ) && $value ? reset( $value ) : null;
		}

		if ( null === $value || '' === $value || false === $value ) {
			return null;
		}

		if ( $field['multiple'] ) {
			$value = is_array( $value ) && $value ? reset( $value ) : null;
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
	private static function format_value( $value, array $field, string $key ) {
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
		}

		if ( in_array( $field['type'], [ 'taxonomy', 'taxonomy_advanced' ], true ) && 'url' === $key ) {
			$url = get_term_link( $value );
			return is_wp_error( $url ) ? null : $url;
		}

		if ( is_object( $value ) ) {
			$value = $value->$key ?? null;
			return is_scalar( $value ) ? (string) $value : null;
		}

		if ( is_array( $value ) ) {
			// Image uses full_url/url; video uses src.
			if ( in_array( $key, [ 'url', 'href' ], true ) ) {
				return $value['full_url'] ?? $value['url'] ?? $value['src'] ?? null;
			}
			$value = $value[ $key ] ?? null;
			return is_scalar( $value ) ? (string) $value : null;
		}

		return is_scalar( $value ) ? (string) $value : null;
	}
}
