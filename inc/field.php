<?php
/**
 * The field base class.
 * This is the parent class of all custom fields defined by the plugin, which defines all the common methods.
 * Fields must inherit this class and overwrite methods with its own.
 *
 * @package Meta Box
 */

/**
 * The field base class.
 */
abstract class RWMB_Field {
	/**
	 * Add actions.
	 */
	public static function add_actions() {
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
	}

	/**
	 * Localize scripts with prevention of loading localized data twice.
	 *
	 * @link https://github.com/rilwis/meta-box/issues/850
	 *
	 * @param string $handle Script handle.
	 * @param string $name Object name.
	 * @param mixed  $data Localized data.
	 */
	public static function localize_script( $handle, $name, $data ) {
		/*
		 * Check with function_exists to make it work in WordPress 4.1.
		 * @link https://github.com/rilwis/meta-box/issues/1009
		 */
		if ( ! function_exists( 'wp_scripts' ) || ! wp_scripts()->get_data( $handle, 'data' ) ) {
			wp_localize_script( $handle, $name, $data );
		}
	}

	/**
	 * Show field HTML
	 * Filters are put inside this method, not inside methods such as "meta", "html", "begin_html", etc.
	 * That ensures the returned value are always been applied filters.
	 * This method is not meant to be overwritten in specific fields.
	 *
	 * @param array $field   Field parameters.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param int   $post_id Post ID.
	 */
	public static function show( $field, $saved, $post_id = 0 ) {
		$meta = self::call( $field, 'meta', $post_id, $saved );
		$meta = self::filter( 'field_meta', $meta, $field, $saved );

		$begin = self::call( $field, 'begin_html', $meta );
		$begin = self::filter( 'begin_html', $begin, $field, $meta );

		// Separate code for cloneable and non-cloneable fields to make easy to maintain.
		if ( $field['clone'] ) {
			$field_html = RWMB_Clone::html( $meta, $field );
		} else {
			// Call separated methods for displaying each type of field.
			$field_html = self::call( $field, 'html', $meta );
			$field_html = self::filter( 'html', $field_html, $field, $meta );
		}

		$end = self::call( $field, 'end_html', $meta );
		$end = self::filter( 'end_html', $end, $field, $meta );

		$html = self::filter( 'wrapper_html', "$begin$field_html$end", $field, $meta );

		// Display label and input in DIV and allow user-defined classes to be appended.
		$classes = "rwmb-field rwmb-{$field['type']}-wrapper " . $field['class'];
		if ( 'hidden' === $field['type'] ) {
			$classes .= ' hidden';
		}
		if ( ! empty( $field['required'] ) ) {
			$classes .= ' required';
		}

		$outer_html = sprintf(
			$field['before'] . '<div class="%s">%s</div>' . $field['after'],
			trim( $classes ),
			$html
		);
		$outer_html = self::filter( 'outer_html', $outer_html, $field, $meta );

		echo $outer_html; // WPCS: XSS OK.
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return '';
	}

	/**
	 * Show begin HTML markup for fields.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function begin_html( $meta, $field ) {
		$field_label = '';
		if ( $field['name'] ) {
			$field_label = sprintf(
				'<div class="rwmb-label">
					<label for="%s">%s</label>
					%s
				</div>',
				esc_attr( $field['id'] ),
				$field['name'],
				self::label_description( $field )
			);
		}

		$data_max_clone = is_numeric( $field['max_clone'] ) && $field['max_clone'] > 1 ? ' data-max-clone=' . $field['max_clone'] : '';

		$input_open = sprintf(
			'<div class="rwmb-input"%s>',
			$data_max_clone
		);

		return $field_label . $input_open;
	}

	/**
	 * Show end HTML markup for fields.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function end_html( $meta, $field ) {
		return RWMB_Clone::add_clone_button( $field ) . self::call( 'input_description', $field ) . '</div>';
	}

	/**
	 * Display field label description.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	protected static function label_description( $field ) {
		$id = $field['id'] ? ' id="' . esc_attr( $field['id'] ) . '-label-description"' : '';
		return $field['label_description'] ? "<p{$id} class='description'>{$field['label_description']}</p>" : '';
	}

	/**
	 * Display field description.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	protected static function input_description( $field ) {
		$id = $field['id'] ? ' id="' . esc_attr( $field['id'] ) . '-description"' : '';
		return $field['desc'] ? "<p{$id} class='description'>{$field['desc']}</p>" : '';
	}

	/**
	 * Get raw meta value.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $field   Field parameters.
	 *
	 * @return mixed
	 */
	public static function raw_meta( $post_id, $field ) {
		if ( empty( $field['id'] ) ) {
			return '';
		}

		$single = $field['clone'] || ! $field['multiple'];
		return get_post_meta( $post_id, $field['id'], $single );
	}

	/**
	 * Get meta value.
	 *
	 * @param int   $post_id Post ID.
	 * @param bool  $saved   Whether the meta box is saved at least once.
	 * @param array $field   Field parameters.
	 *
	 * @return mixed
	 */
	public static function meta( $post_id, $saved, $field ) {
		/**
		 * For special fields like 'divider', 'heading' which don't have ID, just return empty string
		 * to prevent notice error when displaying fields.
		 */
		if ( empty( $field['id'] ) ) {
			return '';
		}

		// Get raw meta.
		$meta = self::call( $field, 'raw_meta', $post_id );

		// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run).
		$meta = ! $saved ? $field['std'] : $meta;

		// Ensue multiple fields are arrays.
		if ( $field['multiple'] ) {
			if ( $field['clone'] ) {
				$meta = (array) $meta;
				foreach ( $meta as $key => $m ) {
					$meta[ $key ] = (array) $m;
				}
			} else {
				$meta = (array) $meta;
			}
		}
		// Escape attributes.
		$meta = self::call( $field, 'esc_meta', $meta );

		// Make sure meta value is an array for clonable and multiple fields.
		if ( $field['clone'] || $field['multiple'] ) {
			if ( empty( $meta ) || ! is_array( $meta ) ) {
				/**
				 * If field is clonable, $meta must be an array with values so that the foreach loop in self::show() runs properly.
				 *
				 * @see self::show()
				 */
				$meta = $field['clone'] ? array( '' ) : array();
			}
		}

		return $meta;
	}

	/**
	 * Escape meta for field output.
	 *
	 * @param mixed $meta Meta value.
	 *
	 * @return mixed
	 */
	public static function esc_meta( $meta ) {
		return is_array( $meta ) ? array_map( __METHOD__, $meta ) : esc_attr( $meta );
	}

	/**
	 * Set value of meta before saving into database.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return int
	 */
	public static function value( $new, $old, $post_id, $field ) {
		return $new;
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
		$name = $field['id'];

		// Remove post meta if it's empty.
		if ( '' === $new || array() === $new ) {
			delete_post_meta( $post_id, $name );
			return;
		}

		// If field is cloneable, value is saved as a single entry in the database.
		if ( $field['clone'] ) {
			// Remove empty values.
			$new = (array) $new;
			foreach ( $new as $k => $v ) {
				if ( '' === $v || array() === $v ) {
					unset( $new[ $k ] );
				}
			}
			// Reset indexes.
			$new = array_values( $new );
			update_post_meta( $post_id, $name, $new );
			return;
		}

		// If field is multiple, value is saved as multiple entries in the database (WordPress behaviour).
		if ( $field['multiple'] ) {
			$old = (array) $old;
			$new = (array) $new;
			$new_values = array_diff( $new, $old );
			foreach ( $new_values as $new_value ) {
				add_post_meta( $post_id, $name, $new_value, false );
			}
			$old_values = array_diff( $old, $new );
			foreach ( $old_values as $old_value ) {
				delete_post_meta( $post_id, $name, $old_value );
			}
			return;
		}

		// Default: just update post meta.
		update_post_meta( $post_id, $name, $new );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, array(
			'id'                => '',
			'name'              => '',
			'label_description' => '',
			'multiple'          => false,
			'std'               => '',
			'desc'              => '',
			'format'            => '',
			'before'            => '',
			'after'             => '',
			'field_name'        => isset( $field['id'] ) ? $field['id'] : '',
			'placeholder'       => '',

			'clone'      => false,
			'max_clone'  => 0,
			'sort_clone' => false,
			'add_button' => __( '+ Add more', 'meta-box' ),

			'class'      => '',
			'disabled'   => false,
			'required'   => false,
			'attributes' => array(),
		) );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = wp_parse_args( $field['attributes'], array(
			'disabled' => $field['disabled'],
			'required' => $field['required'],
			'id'       => $field['id'],
			'class'    => '',
			'name'     => $field['field_name'],
		) );

		$attributes['class'] = implode( ' ', array_merge( array( "rwmb-{$field['type']}" ), (array) $attributes['class'] ) );

		return $attributes;
	}

	/**
	 * Renders an attribute array into an html attributes string.
	 *
	 * @param array $attributes HTML attributes.
	 *
	 * @return string
	 */
	public static function render_attributes( $attributes ) {
		$output = '';

		foreach ( $attributes as $key => $value ) {
			if ( false === $value || '' === $value ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}

			$output .= sprintf( true === $value ? ' %s' : ' %s="%s"', $key, esc_attr( $value ) );
		}

		return $output;
	}

	/**
	 * Get the field value.
	 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
	 * of the field saved in the database, while this function returns more meaningful value of the field, for ex.:
	 * for file/image: return array of file/image information instead of file/image IDs.
	 *
	 * Each field can extend this function and add more data to the returned value.
	 * See specific field classes for details.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	public static function get_value( $field, $args = array(), $post_id = null ) {
		// Some fields does not have ID like heading, custom HTML, etc.
		if ( empty( $field['id'] ) ) {
			return '';
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Get raw meta value in the database, no escape.
		$value  = self::call( $field, 'raw_meta', $post_id );

		// Make sure meta value is an array for cloneable and multiple fields.
		if ( $field['clone'] || $field['multiple'] ) {
			$value = is_array( $value ) && $value ? $value : array();
		}

		return $value;
	}

	/**
	 * Output the field value.
	 * Depends on field value and field types, each field can extend this method to output its value in its own way
	 * See specific field classes for details.
	 *
	 * Note: we don't echo the field value directly. We return the output HTML of field, which will be used in
	 * rwmb_the_field function later.
	 *
	 * @use self::get_value()
	 * @see rwmb_the_value()
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string HTML output of the field
	 */
	public static function the_value( $field, $args = array(), $post_id = null ) {
		$value = self::call( 'get_value', $field, $args, $post_id );
		return self::call( 'format_value', $field, $value );
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field Field parameters.
	 * @param string|array $value The field meta value.
	 * @return string
	 */
	public static function format_value( $field, $value ) {
		if ( ! is_array( $value ) ) {
			return self::call( 'format_single_value', $field, $value );
		}
		$output = '<ul>';
		foreach ( $value as $subvalue ) {
			$output .= '<li>' . self::call( 'format_value', $field, $subvalue ) . '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array  $field Field parameters.
	 * @param string $value The value.
	 * @return string
	 */
	public static function format_single_value( $field, $value ) {
		return $value;
	}

	/**
	 * Call a method of a field.
	 * This should be replaced by static::$method( $args ) in PHP 5.3.
	 *
	 * @return mixed
	 */
	public static function call() {
		$args = func_get_args();

		$check = reset( $args );

		// Params: method name, field, other params.
		if ( is_string( $check ) ) {
			$method = array_shift( $args );
			$field  = reset( $args ); // Keep field as 1st param.
		} else {
			$field  = array_shift( $args );
			$method = array_shift( $args );
			$args[] = $field; // Add field as last param.
		}

		return call_user_func_array( array( self::get_class_name( $field ), $method ), $args );
	}

	/**
	 * Map field types.
	 *
	 * @param array $field Field parameters.
	 * @return string Field mapped type.
	 */
	public static function map_types( $field ) {
		$type = isset( $field['type'] ) ? $field['type'] : 'input';
		$type_map = apply_filters(
			'rwmb_type_map',
			array(
				'file_advanced'  => 'media',
				'plupload_image' => 'image_upload',
				'url'            => 'text',
			)
		);

		return isset( $type_map[ $type ] ) ? $type_map[ $type ] : $type;
	}

	/**
	 * Get field class name.
	 *
	 * @param array $field Field parameters.
	 * @return string Field class name.
	 */
	public static function get_class_name( $field ) {
		$type = self::map_types( $field );
		$type  = str_replace( array( '-', '_' ), ' ', $type );
		$class = 'RWMB_' . ucwords( $type ) . '_Field';
		$class = str_replace( ' ', '_', $class );
		return class_exists( $class ) ? $class : 'RWMB_Input_Field';
	}

	/**
	 * Apply various filters based on field type, id.
	 * Filters:
	 * - rwmb_{$name}
	 * - rwmb_{$field['type']}_{$name}
	 * - rwmb_{$field['id']}_{$name}
	 *
	 * @return mixed
	 */
	public static function filter() {
		$args = func_get_args();

		// 3 first params must be: filter name, value, field. Other params will be used for filters.
		$name  = array_shift( $args );
		$value = array_shift( $args );
		$field = array_shift( $args );

		// List of filters.
		$filters = array(
			'rwmb_' . $name,
			'rwmb_' . $field['type'] . '_' . $name,
		);
		if ( isset( $field['id'] ) ) {
			$filters[] = 'rwmb_' . $field['id'] . '_' . $name;
		}

		// Filter params: value, field, other params. Note: value is changed after each run.
		array_unshift( $args, $field );
		foreach ( $filters as $filter ) {
			$filter_args = $args;
			array_unshift( $filter_args, $value );
			$value = apply_filters_ref_array( $filter, $filter_args );
		}

		return $value;
	}
}
