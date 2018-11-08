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
		$field = wp_parse_args(
			$field,
			array(
				'taxonomy'   => 'category',
				'query_args' => array(),
			)
		);

		// Force taxonomy to be an array.
		$field['taxonomy'] = (array) $field['taxonomy'];

		/*
		 * Set default placeholder:
		 * - If multiple taxonomies: show 'Select a term'.
		 * - If single taxonomy: show 'Select a %taxonomy_name%'.
		 */
		$placeholder = __( 'Select a term', 'meta-box' );
		if ( 1 === count( $field['taxonomy'] ) ) {
			$taxonomy        = reset( $field['taxonomy'] );
			$taxonomy_object = get_taxonomy( $taxonomy );
			if ( false !== $taxonomy_object ) {
				// Translators: %s is the taxonomy singular label.
				$placeholder = sprintf( __( 'Select a %s', 'meta-box' ), strtolower( $taxonomy_object->labels->singular_name ) );
			}
		}
		$field = wp_parse_args(
			$field,
			array(
				'placeholder' => $placeholder,
			)
		);

		// Set default query args.
		$field['query_args'] = wp_parse_args(
			$field['query_args'],
			array(
				'hide_empty' => false,
			)
		);

		// Prevent cloning for taxonomy field, not for child fields (taxonomy_advanced).
		if ( 'taxonomy' == $field['type'] ) {
			$field['clone'] = false;
		}

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Query terms for field options.
	 *
	 * @param  array $field Field settings.
	 * @return array        Field options array.
	 */
	public static function query( $field ) {
		$args  = wp_parse_args(
			$field['query_args'],
			array(
				'taxonomy'               => $field['taxonomy'],
				'hide_empty'             => false,
				'count'                  => false,
				'update_term_meta_cache' => false,
			)
		);
		$terms = get_terms( $args );
		if ( ! is_array( $terms ) ) {
			return array();
		}
		$options = array();
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = array_merge(
				array(
					'value'  => $term->term_id,
					'label'  => $term->name,
					'parent' => $term->parent,
				),
				(array) $term
			);
		}
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
		if ( empty( $field['id'] ) || ! $field['save_field'] ) {
			return;
		}
		$new = array_unique( array_map( 'intval', (array) $new ) );
		$new = empty( $new ) ? null : $new;

		foreach ( $field['taxonomy'] as $taxonomy ) {
			wp_set_object_terms( $post_id, $new, $taxonomy );
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
		if ( empty( $field['id'] ) ) {
			return '';
		}

		$meta = wp_get_object_terms(
			$object_id,
			$field['taxonomy'],
			array(
				'orderby' => 'term_order',
			)
		);
		if ( is_wp_error( $meta ) ) {
			return '';
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
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		$value = wp_get_object_terms(
			$post_id,
			$field['taxonomy'],
			array(
				'orderby' => 'term_order',
			)
		);

		// Get single value if necessary.
		if ( ! $field['clone'] && ! $field['multiple'] && is_array( $value ) ) {
			$value = reset( $value );
		}
		return $value;
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
		return sprintf(
			'<a href="%s" title="%s">%s</a>',
			// @codingStandardsIgnoreLine
			esc_url( get_term_link( $value ) ),
			esc_attr( $value->name ),
			esc_html( $value->name )
		);
	}
}
