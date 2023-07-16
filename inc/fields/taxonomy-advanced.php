<?php
defined( 'ABSPATH' ) || die;

/**
 * Taxonomy advanced field which saves terms' IDs in the post meta in CSV format.
 */
class RWMB_Taxonomy_Advanced_Field extends RWMB_Taxonomy_Field {
	/**
	 * Save terms in form of comma-separated IDs.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return string
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = parent::value( $new, $old, $post_id, $field );

		return implode( ',', $new );
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
		$field['multiple'] = false; // Force to save in 1 row in the database.
		RWMB_Field::save( $new, $old, $post_id, $field );
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
	public static function raw_meta( $object_id, $field, $args = [] ) {
		$args['single'] = true;
		$meta           = RWMB_Field::raw_meta( $object_id, $field, $args );

		if ( empty( $meta ) ) {
			return $field['multiple'] ? [] : '';
		}

		$meta = $field['clone'] ? array_map( 'wp_parse_id_list', $meta ) : wp_parse_id_list( $meta );
		$meta = array_filter( $meta );

		return $meta;
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
	public static function get_value( $field, $args = [], $post_id = null ) {
		$value = RWMB_Field::get_value( $field, $args, $post_id );
		if ( ! $field['clone'] ) {
			return static::terms_info( $field, $value, $args );
		}

		$return = [];
		foreach ( $value as $subvalue ) {
			$return[] = static::terms_info( $field, $subvalue, $args );
		}
		return $return;
	}

	/**
	 * Get terms information.
	 *
	 * @param array  $field    Field parameters.
	 * @param string $term_ids Term IDs, in CSV format.
	 * @param array  $args     Additional arguments (for image size).
	 *
	 * @return array
	 */
	public static function terms_info( $field, $term_ids, $args ) {
		if ( empty( $term_ids ) ) {
			return [];
		}
		$args = wp_parse_args( [
			'include'    => $term_ids,
			'hide_empty' => false,
		], $args );

		$info = get_terms( $field['taxonomy'], $args );
		$info = is_array( $info ) ? $info : [];
		return $field['multiple'] ? $info : reset( $info );
	}
}
