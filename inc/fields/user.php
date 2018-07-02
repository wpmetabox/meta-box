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
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		// Set default field args.
		$field = wp_parse_args( $field, array(
			'placeholder' => __( 'Select an user', 'meta-box' ),
			'query_args'  => array(),
		) );

		// Query posts to set field options.
		$field['options'] = self::query( $field );

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Query users to set field options.
	 * @param  array $field Field settings.
	 * @return array        Field options array.
	 */
	public static function query( $field ) {
		$args = wp_parse_args( $field['query_args'], array(
			'orderby' => 'display_name',
			'order'   => 'asc',
		) );
		$users   = get_users( $args );
		$options = array();
		foreach ( $users as $user ) {
			$options[$user->ID] = array(
				'value' => $user->ID,
				'label' => $user->display_name,
			);
		}
		return $options;
	}

	/**
	 * Get option label.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value Option value.
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value ) {
		$user  = get_userdata( $value );
		return '<a href="' . get_author_posts_url( $value ) . '">' . $user->display_name . '</a>';
	}
}
