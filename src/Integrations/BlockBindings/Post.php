<?php
namespace MetaBox\Integrations\BlockBindings;

use WP_Block;

/**
 * Block bindings source for post fields: meta-box/post-field.
 */
class Post extends Source {
	protected function name(): string {
		return 'meta-box/post-field';
	}

	protected function label(): string {
		return __( 'Meta Box Post Field', 'meta-box' );
	}

	protected function contexts(): array {
		return [ 'postId', 'postType' ];
	}

	protected function object_type(): string {
		return 'post';
	}

	protected function context_key(): string {
		return 'postType';
	}

	protected function get_object_id( array $source_args, WP_Block $block_instance ) {
		return (int) ( $block_instance->context['postId'] ?? 0 ) ?: null;
	}

	protected function get_type( array $source_args, WP_Block $block_instance ): string {
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
	 * Fields for the bindings UI, keyed by post type.
	 * Skip fields with `hide_from_block_bindings`.
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
				if ( empty( $field['id'] ) || $field['hide_from_block_bindings'] ) {
					continue;
				}
				$result[ $post_type ] = array_merge( $result[ $post_type ] ?? [], $this->binding_options( $field ) );
			}
			if ( ! empty( $result[ $post_type ] ) ) {
				$result[ $post_type ] = wp_list_sort( $result[ $post_type ], 'label' );
			}
		}

		return $result;
	}
}
