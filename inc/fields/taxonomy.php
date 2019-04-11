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
		$placeholder   = __( 'Select a term', 'meta-box' );
		$taxonomy_name = self::get_taxonomy_singular_name( $field );
		if ( $taxonomy_name ) {
			// Translators: %s is the taxonomy singular label.
			$placeholder = sprintf( __( 'Select a %s', 'meta-box' ), strtolower( $taxonomy_name ) );
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

		$new_terms = self::add_terms( $field );
		$new       = array_unique( array_map( 'intval', (array) $new ) );
		$new       = array_merge( $new, $new_terms );

		foreach ( $field['taxonomy'] as $taxonomy ) {
			wp_set_object_terms( $post_id, $new, $taxonomy );
		}
	}

	/**
	 * Add new terms if users created some.
	 *
	 * @param array $field Field settings.
	 * @return array
	 */
	protected static function add_terms( $field ) {
		if ( 1 !== count( $field['taxonomy'] ) ) {
			return array();
		}

		$taxonomy = reset( $field['taxonomy'] );

		// @codingStandardsIgnoreLine
		if ( empty( $_POST['rwmb_taxonomy_new'] ) || empty( $_POST['rwmb_taxonomy_new'][ $taxonomy ] ) ) {
			return array();
		}

		$new_terms = array();

		// @codingStandardsIgnoreLine
		$terms = (array) $_POST['rwmb_taxonomy_new'][ $taxonomy ];

		foreach ( $terms as $term ) {
			$new_terms[] = wp_insert_term( $term, $taxonomy );
		}
		$new_terms = array_filter( $new_terms, 'is_array' );
		$new_terms = wp_list_pluck( $new_terms, 'term_id' );

		return $new_terms;
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

	/**
	 * Render "Add New" form
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	public static function add_new_form( $field ) {
		// Only add new term if field has only one taxonomy.
		if ( 1 !== count( $field['taxonomy'] ) ) {
			return '';
		}

		$taxonomy_name = self::get_taxonomy_singular_name( $field );

		// Translators: %s is the taxonomy singular label.
		$button_text = sprintf( __( 'Add New %s', 'meta-box' ), ucwords( $taxonomy_name ) );
		// Translators: %s is the taxonomy singular label.
		$placeholder = sprintf( __( 'Enter new %s name', 'meta-box' ), strtolower( $taxonomy_name ) );

		$html = '
		<div class="rwmb-taxonomy-add">
			<button class="rwmb-taxonomy-add-button">%s</button>
			<div class="rwmb-taxonomy-add-form rwmb-hidden">
				<input type="text" name="rwmb_taxonomy_new[%s][]" size="30" placeholder="%s">
			</div>
		</div>';

		$taxonomy = reset( $field['taxonomy'] );
		$html     = sprintf( $html, esc_html( $button_text ), esc_attr( $taxonomy ), esc_attr( $placeholder ) );

		return $html;
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'rwmb-taxonomy', RWMB_CSS_URL . 'taxonomy.css', '', RWMB_VER );
		wp_enqueue_script( 'rwmb-taxonomy', RWMB_JS_URL . 'taxonomy.js', array( 'jquery' ), RWMB_VER, true );
	}

	/**
	 * Get taxonomy singular name.
	 *
	 * @param array $field Field settings.
	 * @return string
	 */
	protected static function get_taxonomy_singular_name( $field ) {
		if ( 1 !== count( $field['taxonomy'] ) ) {
			return '';
		}
		$taxonomy        = reset( $field['taxonomy'] );
		$taxonomy_object = get_taxonomy( $taxonomy );

		return false === $taxonomy_object ? '' : $taxonomy_object->labels->singular_name;
	}
}
