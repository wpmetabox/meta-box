<?php
defined( 'ABSPATH' ) || die;

/**
 * The post field which allows users to select existing posts.
 */
class RWMB_Post_Field extends RWMB_Object_Choice_Field {
	public static function add_actions() {
		add_action( 'wp_ajax_rwmb_get_posts', [ __CLASS__, 'ajax_get_posts' ] );
		add_action( 'wp_ajax_nopriv_rwmb_get_posts', [ __CLASS__, 'ajax_get_posts' ] );
	}

	public static function ajax_get_posts() {
		check_ajax_referer( 'query' );

		$request = rwmb_request();

		$field = $request->filter_post( 'field', FILTER_DEFAULT, FILTER_FORCE_ARRAY );

		// Required for 'choice_label' filter. See self::filter().
		$field['clone']        = false;
		$field['_original_id'] = $field['id'];

		// Search.
		$field['query_args']['s'] = $request->filter_post( 'term' );

		// Pagination.
		if ( 'query:append' === $request->filter_post( '_type' ) ) {
			$field['query_args']['paged'] = $request->filter_post( 'page', FILTER_SANITIZE_NUMBER_INT );
		}

		// Query the database.
		$items = self::query( null, $field );
		$items = array_values( $items );

		$items = apply_filters( 'rwmb_ajax_get_posts', $items, $field, $request );

		$data = [ 'items' => $items ];

		// More items for pagination.
		$limit = (int) $field['query_args']['posts_per_page'];
		if ( -1 !== $limit && count( $items ) === $limit ) {
			$data['more'] = true;
		}

		wp_send_json_success( $data );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'post_type'  => 'post',
			'parent'     => false,
			'query_args' => [],
		] );

		$field['post_type'] = (array) $field['post_type'];

		/*
		 * Set default placeholder:
		 * - If multiple post types: show 'Select a post'.
		 * - If single post type: show 'Select a %post_type_name%'.
		 */
		$placeholder = __( 'Select a post', 'meta-box' );
		if ( 1 === count( $field['post_type'] ) ) {
			$post_type        = reset( $field['post_type'] );
			$post_type_object = get_post_type_object( $post_type );
			if ( ! empty( $post_type_object ) ) {
				// Translators: %s is the post singular label.
				$placeholder = sprintf( __( 'Select a %s', 'meta-box' ), strtolower( $post_type_object->labels->singular_name ) );
			}
		}
		$field = wp_parse_args( $field, [
			'placeholder' => $placeholder,
		] );

		// Set parent option, which will change field name to `parent_id` to save as post parent.
		if ( $field['parent'] ) {
			$field['multiple']   = false;
			$field['field_name'] = 'parent_id';
		}

		$field = parent::normalize( $field );

		// Set default query args.
		$posts_per_page      = $field['ajax'] ? 10 : -1;
		$field['query_args'] = wp_parse_args( $field['query_args'], [
			'post_type'      => $field['post_type'],
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
		] );

		parent::set_ajax_params( $field );

		return $field;
	}

	public static function query( $meta, array $field ): array {
		$args = wp_parse_args( $field['query_args'], [
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'mb_field_id'            => $field['id'],
		] );

		$meta = wp_parse_id_list( (array) $meta );

		// Query only selected items.
		if ( ! empty( $field['ajax'] ) && ! empty( $meta ) ) {
			$args['posts_per_page'] = count( $meta );
			$args['post__in']       = $meta;
		}

		// Get from cache to prevent same queries.
		$last_changed = wp_cache_get_last_changed( 'posts' );
		$key          = md5( serialize( $args ) );
		$cache_key    = "$key:$last_changed";
		$options      = wp_cache_get( $cache_key, 'meta-box-post-field' );

		if ( false !== $options ) {
			return $options;
		}

		// Only search by title.
		add_filter( 'posts_search', [ __CLASS__, 'search_by_title' ], 10, 2 );
		$query = new WP_Query( $args );
		remove_filter( 'posts_search', [ __CLASS__, 'search_by_title' ] );

		$options = [];
		foreach ( $query->posts as $post ) {
			if ( ! current_user_can( 'read_post', $post ) ) {
				continue;
			}

			$label                = $post->post_title ? $post->post_title : __( '(No title)', 'meta-box' );
			$label                = self::filter( 'choice_label', $label, $field, $post );
			$options[ $post->ID ] = [
				'value'  => $post->ID,
				'label'  => $label,
				'parent' => $post->post_parent,
			];
		}

		// Cache the query.
		wp_cache_set( $cache_key, $options, 'meta-box-post-field' );

		return $options;
	}

	/**
	 * Only search posts by title.
	 * WordPress searches by either title or content which is confused when users can't find their posts.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/posts_search/
	 */
	public static function search_by_title( $search, $wp_query ) {
		global $wpdb;
		if ( empty( $search ) ) {
			return $search;
		}
		$q      = $wp_query->query_vars;
		$n      = ! empty( $q['exact'] ) ? '' : '%';
		$search = [];
		foreach ( (array) $q['search_terms'] as $term ) {
			$term     = esc_sql( $wpdb->esc_like( $term ) );
			$search[] = "($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		}
		if ( empty( $search ) ) {
			return $search;
		}
		$search = ' AND (' . implode( ' AND ', $search ) . ') ';
		if ( ! is_user_logged_in() ) {
			$search .= " AND ($wpdb->posts.post_password = '') ";
		}
		return $search;
	}

	/**
	 * Get meta value.
	 * If field is cloneable, value is saved as a single entry in DB.
	 * Otherwise value is saved as multiple entries (for backward compatibility).
	 *
	 * @see "save" method for better understanding
	 *
	 * @param int   $post_id Post ID.
	 * @param bool  $saved   Is the meta box saved.
	 * @param array $field   Field parameters.
	 *
	 * @return mixed
	 */
	public static function meta( $post_id, $saved, $field ) {
		return $field['parent'] ? wp_get_post_parent_id( $post_id ) : parent::meta( $post_id, $saved, $field );
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array $field   Field parameters.
	 * @param int   $value   The value.
	 * @param array $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param ?int  $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( empty( $value ) ) {
			return '';
		}

		/**
		 * Allow developers to change the value of the post. Used for WPML integration.
		 * @var int|string $value The post ID.
		 * @var array $field The field parameters.
		 * @internal
		 */
		$value = apply_filters( '_rwmb_post_format_single_value', $value, $field );

		$link = $args['link'] ?? 'view';
		$text = get_the_title( $value );

		if ( false === $link ) {
			return $text;
		}
		$url = get_permalink( $value );
		if ( 'edit' === $link ) {
			$url = get_edit_post_link( $value );
		}

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), wp_kses_post( $text ) );
	}

	public static function add_new_form( array $field ): string {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		if ( 1 !== count( $field['post_type'] ) ) {
			return '';
		}

		$post_type = reset( $field['post_type'] );
		if ( ! post_type_exists( $post_type ) ) {
			return '';
		}

		$post_type_object = get_post_type_object( $post_type );

		return sprintf(
			'<a href="#" class="rwmb-post-add-button rwmb-modal-add-button" data-url="%s">%s</a>',
			admin_url( $post_type === 'post' ? 'post-new.php' : 'post-new.php?post_type=' . $post_type ),
			esc_html( $post_type_object->labels->add_new_item )
		);
	}
}
