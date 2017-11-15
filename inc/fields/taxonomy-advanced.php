<?php
/**
 * Taxonomy advanced field which saves terms' IDs in the post meta in CSV format.
 *
 * @package Meta Box
 */

/**
 * The taxonomy advanced field class.
 */
class RWMB_Taxonomy_Advanced_Field extends RWMB_Taxonomy_Field {
	/**
	 * Normalize the field parameters.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, array(
			'clone' => false,
		) );

		$clone          = $field['clone'];
		$field          = parent::normalize( $field );
		$field['clone'] = $clone;

		return $field;
	}

	/**
	 * Get meta values to save.
	 * Save terms in custom field in form of comma-separated IDs, no more by setting post terms.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field ) {
		return implode( ',', array_unique( (array) $new ) );
	}

	/**
	 * Save meta value.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 */
	public static function save( $new, $old, $post_id, $field ) {
		$storage = $field['storage'];

		if ( $new ) {
			$storage->update( $post_id, $field['id'], $new );
		} else {
			$storage->delete( $post_id, $field['id'] );
		}
	}

	/**
	 * Get raw meta value.
	 *
	 * @param int   $object_id Object ID.
	 * @param array $field     Field parameters.
	 * @param array $args      Arguments of {@see rwmb_meta()} helper.
	 *
	 * @return mixed
	 */
	public static function raw_meta( $object_id, $field, $args = array() ) {
		$args['single'] = true;
		$meta = RWMB_Field::raw_meta( $object_id, $field, $args );

		if ( empty( $meta ) ) {
			return $field['multiple'] ? array() : '';
		}
		$meta = is_array( $meta ) ? array_map( 'wp_parse_id_list', $meta ) : wp_parse_id_list( $meta );
		$meta = array_filter( $meta );

		return $field['multiple'] ? $meta : reset( $meta );
	}

	/**
	 * Get the field value.
	 * Return list of post term objects.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Additional arguments.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return array List of post term objects.
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Get raw meta value in the database, no escape.
		$value = self::call( $field, 'raw_meta', $post_id, $args );
		if ( empty( $value ) ) {
			return null;
		}

		// Allow to pass more arguments to "get_terms".
		$args  = wp_parse_args( array(
			'include'    => $value,
			'hide_empty' => false,
		), $args );
		$value = get_terms( $field['taxonomy'], $args );

		// Get single value if necessary.
		if ( ! $field['clone'] && ! $field['multiple'] ) {
			$value = reset( $value );
		}

		return $value;
	}
}
