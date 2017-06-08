<?php
/**
 * The taxonomy field which aims to replace the built-in WordPress taxonomy UI with more options.
 *
 * @package Meta Box
 */

/**
 * Taxonomy field class which set post terms when saving.
 */
class RWMB_Taxonomy_Field extends RWMB_Object_Choice_Field {
	/**
	 * Add default value for 'taxonomy' field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		// Backwards compatibility with field args.
		if ( isset( $field['options']['args'] ) ) {
			$field['query_args'] = $field['options']['args'];
		}
		if ( isset( $field['options']['taxonomy'] ) ) {
			$field['taxonomy'] = $field['options']['taxonomy'];
		}
		if ( isset( $field['options']['type'] ) ) {
			$field['field_type'] = $field['options']['type'];
		}

		// Set default field args.
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'taxonomy'   => 'category',
		) );

		// Set default query args.
		$field['query_args'] = wp_parse_args( $field['query_args'], array(
			'hide_empty' => false,
		) );

		/*
		 * Set default placeholder:
		 * - If multiple taxonomies: show 'Select a term'.
		 * - If single taxonomy: show 'Select a %taxonomy_name%'.
		 */
		if ( empty( $field['placeholder'] ) ) {
			$field['placeholder'] = __( 'Select a term', 'meta-box' );
			if ( is_string( $field['taxonomy'] ) && taxonomy_exists( $field['taxonomy'] ) ) {
				$taxonomy_object = get_taxonomy( $field['taxonomy'] );

				// Translators: %s is the taxonomy singular label.
				$field['placeholder'] = sprintf( __( 'Select a %s', 'meta-box' ), $taxonomy_object->labels->singular_name );
			}
		}

		// Prevent cloning for taxonomy field.
		$field['clone'] = false;

		return $field;
	}

	/**
	 * Get field names of object to be used by walker.
	 *
	 * @return array
	 */
	public static function get_db_fields() {
		return array(
			'parent' => 'parent',
			'id'     => 'term_id',
			'label'  => 'name',
		);
	}

	/**
	 * Get options for selects, checkbox list, etc via the terms.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function get_options( $field ) {
		$options = get_terms( $field['taxonomy'], $field['query_args'] );
		return $options;
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
		$new = array_unique( array_map( 'intval', (array) $new ) );
		$new = empty( $new ) ? null : $new;
		wp_set_object_terms( $post_id, $new, $field['taxonomy'] );
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
		if ( empty( $field['id'] ) ) {
			return '';
		}

		$meta = get_the_terms( $object_id, $field['taxonomy'] );

		if ( ! is_array( $meta ) || empty( $meta ) ) {
			return $field['multiple'] ? array() : '';
		}

		$meta = wp_list_pluck( $meta, 'term_id' );

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
		$value = get_the_terms( $post_id, $field['taxonomy'] );

		// Get single value if necessary.
		if ( ! $field['clone'] && ! $field['multiple'] && is_array( $value ) ) {
			$value = reset( $value );
		}
		return $value;
	}

	/**
	 * Get option label.
	 *
	 * @param array  $field Field parameters.
	 * @param object $value The term object.
	 *
	 * @return string
	 */
	public static function get_option_label( $field, $value ) {
		return sprintf(
			'<a href="%s" title="%s">%s</a>',
			// @codingStandardsIgnoreLine
			esc_url( get_term_link( $value ) ),
			esc_attr( $value->name ),
			$value->name
		);
	}
}
