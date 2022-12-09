<?php
/**
 * The user select field.
 */
class RWMB_User_Field extends RWMB_Object_Choice_Field {
	public static function add_actions() {
		add_action( 'wp_ajax_rwmb_get_users', [ __CLASS__, 'ajax_get_users' ] );
		add_action( 'wp_ajax_nopriv_rwmb_get_users', [ __CLASS__, 'ajax_get_users' ] );
		add_action( 'clean_user_cache', [ __CLASS__, 'update_cache' ] );
	}

	public static function ajax_get_users() {
		check_ajax_referer( 'query' );

		$request = rwmb_request();

		$field = $request->filter_post( 'field', FILTER_DEFAULT, FILTER_FORCE_ARRAY );

		// Required for 'choice_label' filter. See self::filter().
		$field['clone']        = false;
		$field['_original_id'] = $field['id'];

		// Search.
		$term = (string) $request->filter_post( 'term' );
		if ( $term ) {
			$field['query_args']['search'] = "*{$term}*";
		}

		// Pagination.
		$limit = $field['query_args']['number'] ?? 0;
		$limit = (int) $limit;
		if ( $limit && 'query:append' === $request->filter_post( '_type' ) ) {
			$field['query_args']['paged'] = $request->filter_post( 'page', FILTER_SANITIZE_NUMBER_INT );
		}

		// Query the database.
		$items = self::query( null, $field );
		$items = array_values( $items );

		$data = [ 'items' => $items ];

		// More items for pagination.
		if ( $limit && count( $items ) === $limit ) {
			$data['more'] = true;
		}

		wp_send_json_success( $data );
	}

	/**
	 * Update object cache to make sure query method below always get the fresh list of users.
	 * Unlike posts and terms, WordPress doesn't set 'last_changed' for users.
	 * So we have to do it ourselves.
	 *
	 * @see clean_post_cache()
	 */
	public static function update_cache() {
		wp_cache_set( 'last_changed', microtime(), 'users' );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		// Set default field args.
		$field = wp_parse_args( $field, [
			'placeholder'   => __( 'Select a user', 'meta-box' ),
			'query_args'    => [],
			'display_field' => 'display_name',
		] );

		$field = parent::normalize( $field );

		// Set default query args.
		$limit               = $field['ajax'] ? 10 : 0;
		$field['query_args'] = wp_parse_args( $field['query_args'], [
			'number' => $limit,
		] );

		parent::set_ajax_params( $field );

		if ( $field['ajax'] ) {
			$field['js_options']['ajax_data']['field']['display_field'] = $field['display_field'];
		}

		return $field;
	}

	public static function query( $meta, array $field ) : array {
		$display_field = $field['display_field'];
		$args          = wp_parse_args( $field['query_args'], [
			'orderby' => $display_field,
			'order'   => 'asc',
		] );

		$meta = wp_parse_id_list( (array) $meta );

		// Query only selected items.
		if ( ! empty( $field['ajax'] ) && ! empty( $meta ) ) {
			$args['include'] = $meta;
		}

		// Get from cache to prevent same queries.
		$last_changed = wp_cache_get_last_changed( 'users' );
		$key          = md5( serialize( $args ) );
		$cache_key    = "$key:$last_changed";
		$options      = wp_cache_get( $cache_key, 'meta-box-user-field' );
		if ( false !== $options ) {
			return $options;
		}

		$users   = get_users( $args );
		$options = [];
		foreach ( $users as $user ) {
			$label = $user->$display_field ? $user->$display_field : __( '(No title)', 'meta-box' );
			$label = self::filter( 'choice_label', $label, $field, $user );

			$options[ $user->ID ] = [
				'value' => $user->ID,
				'label' => $label,
			];
		}

		// Cache the query.
		wp_cache_set( $cache_key, $options, 'meta-box-user-field' );

		return $options;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param int      $value   User ID.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		if ( empty( $value ) ) {
			return '';
		}

		$link          = $args['link'] ?? 'view';
		$user          = get_userdata( $value );
		$display_field = $field['display_field'];
		$text          = $user->$display_field;

		if ( false === $link ) {
			return $text;
		}
		$url = get_author_posts_url( $value );
		if ( 'edit' === $link ) {
			$url = get_edit_user_link( $value );
		}

		return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $text ) );
	}
}
