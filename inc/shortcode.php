<?php
use MetaBox\Support\Arr;

class RWMB_Shortcode {
	/**
	 * Settings pages from `mb_settings_pages`, cached for this request.
	 *
	 * @var array|null
	 */
	private $settings_pages = null;

	public function init(): void {
		add_shortcode( 'rwmb_meta', [ $this, 'register_shortcode' ] );
	}

	/**
	 * Render the `[rwmb_meta]` shortcode.
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

		// Do not pass id/object_id to rwmb_the_value() as field args.
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
	 * @param int|string|null $object_id Object ID.
	 *                                   For `post`, null uses the current post.
	 *                                   For other types, null returns an empty string.
	 * @param array           $atts      Shortcode attributes.
	 * @return mixed
	 */
	private function get_value( string $field_id, $object_id, array $atts ) {
		$object_type = ! empty( $atts['object_type'] ) ? $atts['object_type'] : 'post';

		// Non-post types require object_id.
		if ( 'post' !== $object_type && is_null( $object_id ) ) {
			return '';
		}

		if ( ! is_null( $object_id ) && ! $this->check_object_permission( $field_id, $object_id, $atts ) ) {
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
	 * Check whether the current user can read the object.
	 *
	 * @param string     $field_id  Field ID.
	 * @param int|string $object_id Object ID.
	 * @param array      $atts      Shortcode attributes.
	 * @return bool
	 */
	private function check_object_permission( string $field_id, $object_id, array $atts ): bool {
		$object_type = ! empty( $atts['object_type'] ) ? $atts['object_type'] : 'post';
		$allowed     = $this->user_can_read_object( $object_type, $object_id, $atts );

		/**
		 * Filters whether the shortcode can read the object.
		 *
		 * @param bool       $allowed     Whether access is allowed.
		 * @param string     $object_type Object type.
		 * @param int|string $object_id   Object ID.
		 * @param array      $atts        Shortcode attributes without id and object_id.
		 * @param string     $field_id    Field ID.
		 */
		return (bool) apply_filters( 'rwmb_meta_shortcode_permission', $allowed, $object_type, $object_id, $atts, $field_id );
	}

	/**
	 * Dispatch the read check by object type.
	 *
	 * @param string     $object_type Object type.
	 * @param int|string $object_id   Object ID.
	 * @param array      $atts        Shortcode attributes.
	 * @return bool
	 */
	private function user_can_read_object( string $object_type, $object_id, array $atts ): bool {
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
				return $this->user_can_read_setting( (string) $object_id, false );

			case 'network_setting':
				return $this->user_can_read_setting( (string) $object_id, true );

			case 'model':
				return $this->user_can_read_model( $atts );

			default:
				return false;
		}
	}

	/**
	 * Check whether the current user can read a post.
	 *
	 * @param int|string $post_id Post ID.
	 * @return bool
	 */
	private function user_can_read_post( $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return false;
		}

		// A password post is still publicly viewable, so check the password cookie first.
		// If the visitor has not entered the password, require read_post. Guests fail that check.
		if ( post_password_required( $post ) ) {
			return current_user_can( $post_type_object->cap->read_post, $post_id );
		}

		if ( is_post_publicly_viewable( $post ) ) {
			return true;
		}

		return current_user_can( $post_type_object->cap->read_post, $post_id );
	}

	/**
	 * Check whether the current user can read a term.
	 *
	 * @param int|string $term_id Term ID.
	 * @return bool
	 */
	private function user_can_read_term( $term_id ): bool {
		$term = get_term( $term_id );
		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}

		// Public terms are readable. Other terms need edit_term.
		return is_term_publicly_viewable( $term ) || current_user_can( 'edit_term', $term_id );
	}

	/**
	 * Check whether the current user can read a comment.
	 *
	 * @param int|string $comment_id Comment ID.
	 * @return bool
	 */
	private function user_can_read_comment( $comment_id ): bool {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return false;
		}

		// Approved comments on a readable post are readable. Other comments need edit_comment.
		if ( '1' === (string) $comment->comment_approved && $this->user_can_read_post( $comment->comment_post_ID ) ) {
			return true;
		}

		return current_user_can( 'edit_comment', $comment_id );
	}

	/**
	 * Check whether the current user can read a settings page.
	 *
	 * @param string $option_name Settings page option name.
	 * @param bool   $network     Whether to match a network settings page.
	 * @return bool
	 */
	private function user_can_read_setting( string $option_name, bool $network = false ): bool {
		foreach ( $this->get_settings_pages() as $page ) {
			$is_network = ! empty( $page['network'] );
			if ( $network !== $is_network ) {
				continue;
			}

			// MB Settings Page uses id when option_name is empty.
			$page_option = ! empty( $page['option_name'] ) ? $page['option_name'] : ( $page['id'] ?? '' );
			if ( '' === $page_option || $option_name !== $page_option ) {
				continue;
			}

			// Network pages default to manage_network_options so site admins cannot read network data.
			$default_cap = $network ? 'manage_network_options' : 'edit_theme_options';
			$capability  = ! empty( $page['capability'] ) ? $page['capability'] : $default_cap;
			return current_user_can( $capability );
		}

		return false;
	}

	/**
	 * Check whether the current user can read a custom table model field.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return bool
	 */
	private function user_can_read_model( array $atts ): bool {
		if ( ! class_exists( '\MetaBox\CustomTable\Model\Factory' ) ) {
			return false;
		}

		// Same as rwmb_meta(): type is the model name.
		$model_name = $atts['type'] ?? '';
		$model      = $model_name ? \MetaBox\CustomTable\Model\Factory::get( $model_name ) : null;
		if ( ! $model ) {
			return false;
		}

		$capability = $model->capability ?? 'edit_posts';
		return current_user_can( $capability ?: 'edit_posts' );
	}

	/**
	 * Return registered settings pages. Loads them once per request.
	 *
	 * @return array
	 */
	private function get_settings_pages(): array {
		if ( null === $this->settings_pages ) {
			$pages                = apply_filters( 'mb_settings_pages', [] );
			$this->settings_pages = is_array( $pages ) ? $pages : [];
		}

		return $this->settings_pages;
	}
}
