<?php
/**
 * The user select field.
 *
 * @package Meta Box
 */

/**
 * User field class.
 */
class RWMB_User_Field extends RWMB_Object_Choice_Field {
	/**
	 * Add actions.
	 */
	public static function add_actions() {
		add_action( 'clean_user_cache', array( __CLASS__, 'update_cache' ) );
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
		$field = wp_parse_args(
			$field,
			array(
				'placeholder'   => __( 'Select an user', 'meta-box' ),
				'query_args'    => array(),
				'display_field' => 'display_name',
			)
		);

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Query users for field options.
	 *
	 * @param  array $field Field settings.
	 * @return array        Field options array.
	 */
	public static function query( $field ) {
		$display_field = $field['display_field'];
		$args          = wp_parse_args(
			$field['query_args'],
			array(
				'orderby' => $display_field,
				'order'   => 'asc',
				'fields'  => array( 'ID', $display_field ),
			)
		);

		// Get from cache to prevent same queries.
		$last_changed = wp_cache_get_last_changed( 'users' );
		$key          = md5( serialize( $args ) );
		$cache_key    = "$key:$last_changed";
		$options      = wp_cache_get( $cache_key, 'meta-box-user-field' );
		if ( false !== $options ) {
			return $options;
		}

		$users   = get_users( $args );
		$options = array();
		foreach ( $users as $user ) {
			$options[ $user->ID ] = array_merge(
				array(
					'value' => $user->ID,
					'label' => $user->$display_field,
				),
				(array) $user
			);
		}

		// Cache the query.
		wp_cache_set( $cache_key, $options, 'meta-box-user-field' );

		return $options;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		$display_field = $field['display_field'];
		$user          = get_userdata( $value );
		return '<a href="' . esc_url( get_author_posts_url( $value ) ) . '">' . esc_html( $user->$display_field ) . '</a>';
	}
}
