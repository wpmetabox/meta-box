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

	/**
	 * Fields storing attachment IDs. Their value can map to url/id/alt/title/caption.
	 */
	private const ATTACHMENT_TYPES = [
		'single_image',
		'image',
		'image_advanced',
		'image_upload',
		'file',
		'file_advanced',
		'file_upload',
		'video',
	];

	private const NUMERIC_TYPES = [ 'number', 'range', 'slider' ];

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
	 * Attachment fields are listed as both string (url/alt/…) and number (id).
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

				$item = [
					'label' => $field['name'] ?: $field['id'],
					'args'  => [ 'id' => $field['id'] ],
				];

				$types = self::attribute_types( $field['type'] );
				foreach ( $types as $type ) {
					$result[ $post_type ][] = $item + [ 'type' => $type ];
				}
			}
		}

		return $result;
	}

	/**
	 * Block attribute types a field can bind to.
	 *
	 * @return string[]
	 */
	private static function attribute_types( string $field_type ): array {
		if ( in_array( $field_type, self::ATTACHMENT_TYPES, true ) ) {
			return [ 'string', 'number' ];
		}
		if ( in_array( $field_type, self::NUMERIC_TYPES, true ) ) {
			return [ 'string', 'number' ];
		}
		return [ 'string' ];
	}

	/**
	 * Resolve the field value for a bound block attribute (front-end render).
	 *
	 * @param array     $source_args    Expects `id` (field id).
	 * @param WP_Block  $block_instance Block instance.
	 * @param string    $attribute_name Bound attribute.
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

		return self::format_for_attribute( $value, $attribute_name );
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
	 * Map a single field value to the bound block attribute.
	 *
	 * @param mixed  $value          Single field value.
	 * @param string $attribute_name Block attribute.
	 * @return mixed
	 */
	private static function format_for_attribute( $value, string $attribute_name ) {
		if ( is_array( $value ) ) {
			if ( 'id' === $attribute_name ) {
				return isset( $value['ID'] ) ? (float) $value['ID'] : null;
			}
			if ( in_array( $attribute_name, [ 'url', 'href' ], true ) ) {
				return $value['full_url'] ?? $value['url'] ?? null;
			}
			return isset( $value[ $attribute_name ] ) ? (string) $value[ $attribute_name ] : null;
		}

		if ( 'id' === $attribute_name && is_numeric( $value ) ) {
			return (float) $value;
		}

		return is_scalar( $value ) ? (string) $value : null;
	}
}
