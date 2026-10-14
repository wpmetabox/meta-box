<?php
use MetaBox\Support\Arr;

class RWMB_Shortcode {
	public function init(): void {
		add_shortcode( 'rwmb_meta', [ $this, 'register_shortcode' ] );
	}

	/**
	 * Render the [rwmb_meta] shortcode.
	 *
	 * @param array|string $atts Shortcode attributes. WordPress may pass an empty string.
	 * @return mixed Field value for output.
	 */
	public function register_shortcode( $atts ) {
		$atts = wp_parse_args( $atts, [
			'id'                => '',
			'object_id'         => null,
			'attribute'         => '',
			'render_shortcodes' => 'true',
		] );
		Arr::change_key( $atts, 'post_id', 'object_id' );
		Arr::change_key( $atts, 'meta_key', 'id' );

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$field_id  = $atts['id'];
		$object_id = $atts['object_id'];

		unset( $atts['id'], $atts['object_id'] );

		$value = $this->get_value( $field_id, $object_id, $atts );
		$value = 'true' === $atts['render_shortcodes'] ? do_shortcode( $value ) : $value;

		$secure = apply_filters( 'rwmb_meta_shortcode_secure', true, $field_id, $atts, $object_id );
		$secure = apply_filters( "rwmb_meta_shortcode_secure_{$field_id}", $secure, $atts, $object_id );

		if ( $secure ) {
			$value = wp_kses_post( $value );
		}

		return $value;
	}

	/**
	 * Get the field value for the shortcode.
	 *
	 * @param string          $field_id  Field ID.
	 * @param int|string|null $object_id Object ID. Null uses the current object.
	 * @param array           $atts      Shortcode attributes.
	 * @return mixed
	 */
	private function get_value( string $field_id, $object_id, array $atts ) {
		// When object_id is set, make sure the current user can view that object.
		if ( ! is_null( $object_id ) && ! $this->check_object_permission( $object_id, $atts ) ) {
			return '';
		}

		$attribute = $atts['attribute'];
		if ( ! $attribute ) {
			return rwmb_the_value( $field_id, $atts, $object_id, false );
		}

		$value = rwmb_get_value( $field_id, $atts, $object_id );

		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) ) {
			return $value->$attribute;
		}

		if ( isset( $value[ $attribute ] ) ) {
			return $value[ $attribute ];
		}

		$value = wp_list_pluck( $value, $attribute );
		$value = implode( ',', array_filter( $value ) );

		return $value;
	}

	/**
	 * Check if the current user can read the object in the shortcode.
	 *
	 * @param int|string $object_id Object ID.
	 * @param array      $atts      Shortcode attributes.
	 * @return bool
	 */
	private function check_object_permission( $object_id, array $atts ): bool {
		$object_type = ! empty( $atts['object_type'] ) ? $atts['object_type'] : 'post';
		$allowed     = $this->user_can_read_object( $object_type, $object_id );

		// Allow or deny access. Example: add_filter( 'rwmb_meta_shortcode_permission', '__return_true' );
		return (bool) apply_filters( 'rwmb_meta_shortcode_permission', $allowed, $object_type, $object_id, $atts );
	}

	/**
	 * Route the read check to the handler for the object type.
	 *
	 * @param string     $object_type Object type.
	 * @param int|string $object_id   Object ID.
	 * @return bool
	 */
	private function user_can_read_object( string $object_type, $object_id ): bool {
		switch ( $object_type ) {
			case 'post':
				return $this->user_can_read_post( $object_id );

			case 'term':
				return $this->user_can_read_term( $object_id );

			case 'comment':
				return $this->user_can_read_comment( $object_id );

			case 'user':
				return current_user_can( 'edit_user', $object_id );

			case 'setting':
				return $this->user_can_read_setting( (string) $object_id );

			default:
				return false;
		}
	}

	/**
	 * Check if the current user can read a post.
	 *
	 * @param int|string $post_id Post ID.
	 * @return bool
	 */
	private function user_can_read_post( $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		// Allow a published post that has no password.
		if ( 'publish' === $post->post_status && ! post_password_required( $post ) ) {
			return true;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return false;
		}

		return current_user_can( $post_type_object->cap->read_post, $post_id );
	}

	/**
	 * Check if the current user can read a term.
	 *
	 * @param int|string $term_id Term ID.
	 * @return bool
	 */
	private function user_can_read_term( $term_id ): bool {
		$term = get_term( $term_id );
		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		// Allow a term that is publicly viewable. For other terms, check edit_term.
		return is_term_publicly_viewable( $term ) || current_user_can( 'edit_term', $term_id );
	}

	/**
	 * Check if the current user can read a comment.
	 *
	 * @param int|string $comment_id Comment ID.
	 * @return bool
	 */
	private function user_can_read_comment( $comment_id ): bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		// Allow an approved comment when the parent post is readable. For other comments, check edit_comment.
		if ( '1' === (string) $comment->comment_approved && $this->user_can_read_post( $comment->comment_post_ID ) ) {
			return true;
		}

		return current_user_can( 'edit_comment', $comment_id );
	}

	/**
	 * Check if the current user can read a settings page by option name.
	 *
	 * @param string $option_name Settings page option name.
	 * @return bool
	 */
	private function user_can_read_setting( string $option_name ): bool {
		foreach ( apply_filters( 'mb_settings_pages', [] ) as $page ) {
			// Match Factory: empty option_name falls back to id.
			$page_option = ! empty( $page['option_name'] ) ? $page['option_name'] : ( $page['id'] ?? '' );
			if ( '' === $page_option || $option_name !== $page_option ) {
				continue;
			}

			// Check the capability of this settings page.
			$capability = $page['capability'] ?? 'edit_theme_options';
			return current_user_can( $capability ?: 'edit_theme_options' );
		}

		return false;
	}
}
