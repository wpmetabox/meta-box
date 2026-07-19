<?php
namespace MetaBox\Integrations;

use RWMB_Field;
use WP_Block;

/**
 * Register a custom block bindings source: meta-box/field.
 *
 * Fields opt in with `'block_bindings' => true`.
 * The front-end value is resolved in PHP; the editor only needs the fields list to show the source in the bindings UI.
 */
class BlockBindings {
	public const SOURCE_NAME = 'meta-box/field';
	private const VALUE_KEYS = [
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
		$fields = $this->get_editor_fields();
		if ( ! $fields ) {
			return;
		}

		wp_enqueue_script( 'rwmb-block-bindings', RWMB_JS_URL . 'block-bindings.js', [ 'wp-blocks', 'wp-i18n' ], RWMB_VER, true );
		wp_localize_script( 'rwmb-block-bindings', 'rwmbBlockBindings', [
			'fields' => $fields,
		] );
	}

	/**
	 * Fields (opted in via `block_bindings`) for the editor UI, keyed by post type.
	 *
	 * Structured fields (image, map, post, user, …) expose selectable value keys via `args.key`.
	 * Scalar fields are listed once as `string`.
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
				$result[ $post_type ] = array_merge( $result[ $post_type ] ?? [], $this->binding_options( $field ) );
			}
		}

		return $result;
	}

	/**
	 * Binding options for one field (label + type + args).
	 *
	 * @return list<array{label: string, type: string, args: array{id: string, key?: string}}>
	 */
	private function binding_options( array $field ): array {
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
	private function value_key_options( string $name, string $id, array $keys, array $labels = [] ): array {
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

	private function value_key_label( string $key ): string {
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
	 * @param array    $source_args    Field id and optional value key.
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

		// Reuse the already-fetched $field instead of rwmb_get_value(), which would look it up again.
		$value = $this->get_single_value( RWMB_Field::call( 'get_value', $field, $args, $post_id ), $field );
		if ( $this->is_empty( $value ) ) {
			return null;
		}

		// `key` picks a part of a structured value, falling back to the bound attribute name (e.g. Image block `url`).
		return $this->format_value( $value, $field, $source_args['key'] ?? $attribute_name );
	}

	/**
	 * Reduce clone / multiple values to a single unit value.
	 *
	 * @param mixed $value Value from rwmb_get_value().
	 * @param array $field Field settings.
	 * @return mixed
	 */
	private function get_single_value( $value, array $field ) {
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
	private function format_value( $value, array $field, string $key ) {
		if ( 'post' === $field['type'] ) {
			$post = get_post( $value );
			if ( ! $post ) {
				return null;
			}
			if ( 'url' === $key ) {
				return esc_url( get_permalink( $post ) );
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
			// user_url is user-controlled, so escape it as a URL.
			if ( 'user_url' === $key ) {
				return esc_url( $value->user_url );
			}
		}

		if ( in_array( $field['type'], [ 'taxonomy', 'taxonomy_advanced' ], true ) && 'url' === $key ) {
			$url = get_term_link( $value );
			return is_wp_error( $url ) ? null : esc_url( $url );
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

	private function is_empty( $value ): bool {
		return null === $value || '' === $value || false === $value;
	}

	private function to_string( $value ): ?string {
		return is_scalar( $value ) ? (string) $value : null;
	}
}
