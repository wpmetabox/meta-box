<?php
namespace MetaBox\Integrations;

/**
 * Block Bindings source: meta-box/field.
 */
class BlockBindings {
	public const SOURCE_NAME = 'meta-box/field';

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
		wp_enqueue_script(
			'rwmb-block-bindings',
			RWMB_JS_URL . 'block-bindings.js',
			[ 'wp-blocks', 'wp-data', 'wp-core-data', 'wp-i18n' ],
			RWMB_VER,
			true
		);
		wp_localize_script( 'rwmb-block-bindings', 'rwmbBlockBindings', [
			'fields' => $this->get_editor_fields(),
		] );
	}

	/**
	 * Editor field list keyed by post type.
	 * Attachment fields are listed as both string (url/alt/…) and number (id).
	 */
	private function get_editor_fields(): array {
		$result = [];

		foreach ( rwmb_get_registry( 'field' )->get_by_object_type( 'post' ) as $post_type => $fields ) {
			$list = [];

			foreach ( $fields as $field ) {
				if ( empty( $field['id'] ) || ! \RWMB_Helpers_Field::get_class( $field )::can_register_meta() ) {
					continue;
				}

				$item = [
					'label' => $field['name'] ?: $field['id'],
					'args'  => [ 'id' => $field['id'] ],
				];

				if ( in_array( $field['type'], self::ATTACHMENT_TYPES, true ) ) {
					$list[] = $item + [ 'type' => 'string' ];
					$list[] = $item + [ 'type' => 'number' ];
					continue;
				}

				$type = \RWMB_Field::call( 'get_full_schema', $field )['type'] ?? 'string';
				if ( in_array( $type, [ 'array', 'object' ], true ) ) {
					continue;
				}

				$list[] = $item + [ 'type' => 'integer' === $type ? 'number' : $type ];
			}

			$result[ $post_type ] = $list;
		}

		return $result;
	}

	/**
	 * @param array     $source_args    Expects `id` (field id).
	 * @param \WP_Block $block_instance Block instance.
	 * @param string    $attribute_name Bound attribute.
	 * @return mixed
	 */
	public function get_value( array $source_args, $block_instance, string $attribute_name ) {
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
		if ( ! $field || ! \RWMB_Helpers_Field::get_class( $field )::can_register_meta() ) {
			return null;
		}

		$value = self::unwrap_value( rwmb_get_value( $field_id, $args, $post_id ) );
		return ( null === $value || '' === $value || false === $value )
			? null
			: self::format_for_attribute( $value, $attribute_name );
	}

	/**
	 * Use the first value for cloneable / multiple fields.
	 *
	 * @param mixed $value Value from rwmb_get_value().
	 * @return mixed
	 */
	public static function unwrap_value( $value ) {
		while ( is_array( $value ) ) {
			if ( [] === $value ) {
				return null;
			}
			// Keep file-info arrays.
			if ( isset( $value['ID'] ) || isset( $value['url'] ) || isset( $value['full_url'] ) ) {
				break;
			}
			$value = reset( $value );
		}

		return $value;
	}

	/**
	 * @param mixed  $value          Unwrapped field value.
	 * @param string $attribute_name Block attribute.
	 * @return mixed
	 */
	public static function format_for_attribute( $value, string $attribute_name ) {
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
