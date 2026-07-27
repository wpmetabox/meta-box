<?php
namespace MetaBox\Integrations\BlockBindings;

use WP_Block;

/**
 * Block bindings source for post fields: meta-box/post-field.
 */
class Post extends Source {
	public function name(): string {
		return 'meta-box/post-field';
	}

	public function label(): string {
		return __( 'Meta Box Post Field', 'meta-box' );
	}

	public function uses_context(): array {
		return [ 'postId', 'postType' ];
	}

	public function object_type(): string {
		return 'post';
	}

	public function context_key(): string {
		return 'postType';
	}

	protected function get_object_id( WP_Block $block_instance ) {
		return (int) ( $block_instance->context['postId'] ?? 0 ) ?: null;
	}

	protected function get_type( WP_Block $block_instance ): string {
		return $block_instance->context['postType'] ?? '';
	}

	protected function has_permission( $object_id, WP_Block $block_instance ): bool {
		$post = get_post( (int) $object_id );
		if ( ! $post ) {
			return false;
		}

		if ( post_password_required( $post ) ) {
			return false;
		}

		if ( is_post_publicly_viewable( $post ) ) {
			return true;
		}

		return current_user_can( 'read_post', $object_id );
	}

	/**
	 * Fields (opted in via `block_bindings`) for the bindings UI, keyed by post type.
	 *
	 * Structured fields (image, map, post, user, …) expose selectable value keys via `args.key`.
	 * Scalar fields are listed once as `string`.
	 *
	 * @return array<string, array>
	 */
	protected function get_fields(): array {
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
}
