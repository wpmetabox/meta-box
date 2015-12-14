<?php
if ( ! class_exists( 'RWMB_Field ' ) )
{
	class RWMB_Field
	{
		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
		}

		/**
		 * Show field HTML
		 * Filters are put inside this method, not inside methods such as "meta", "html", "begin_html", etc.
		 * That ensures the returned value are always been applied filters
		 * This method is not meant to be overwritten in specific fields
		 *
		 * @param array $field
		 * @param bool  $saved
		 *
		 * @return string
		 */
		static function show( $field, $saved )
		{
			$post    = get_post();
			$post_id = isset( $post->ID ) ? $post->ID : 0;

			$field_class = RW_Meta_Box::get_class_name( $field );
			$meta        = call_user_func( array( $field_class, 'meta' ), $post_id, $saved, $field );

			// Apply filter to field meta value
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$meta = apply_filters( 'rwmb_field_meta', $meta, $field, $saved );
			$meta = apply_filters( "rwmb_{$field['type']}_meta", $meta, $field, $saved );
			$meta = apply_filters( "rwmb_{$field['id']}_meta", $meta, $field, $saved );

			$type = $field['type'];
			$id   = $field['id'];

			$begin = call_user_func( array( $field_class, 'begin_html' ), $meta, $field );

			// Apply filter to field begin HTML
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$begin = apply_filters( 'rwmb_begin_html', $begin, $field, $meta );
			$begin = apply_filters( "rwmb_{$type}_begin_html", $begin, $field, $meta );
			$begin = apply_filters( "rwmb_{$id}_begin_html", $begin, $field, $meta );

			// Separate code for cloneable and non-cloneable fields to make easy to maintain

			// Cloneable fields
			if ( $field['clone'] )
			{
				$field_html = '';

				/**
				 * Note: $meta must contain value so that the foreach loop runs!
				 * @see meta()
				 */
				foreach ( $meta as $index => $sub_meta )
				{
					$sub_field               = $field;
					$sub_field['field_name'] = $field['field_name'] . "[{$index}]";
					if ( $index > 0 )
					{
						if ( isset( $sub_field['address_field'] ) )
							$sub_field['address_field'] = $field['address_field'] . "_{$index}";
						$sub_field['id'] = $field['id'] . "_{$index}";
					}
					if ( $field['multiple'] )
						$sub_field['field_name'] .= '[]';

					// Wrap field HTML in a div with class="rwmb-clone" if needed
					$class = "rwmb-clone rwmb-{$field['type']}-clone";
					if ( $field['sort_clone'] )
					{
						$class .= ' rwmb-sort-clone';
					}
					$input_html = "<div class='$class'>";

					// Drag clone icon
					if ( $field['sort_clone'] )
						$input_html .= "<a href='javascript:;' class='rwmb-clone-icon'></a>";

					// Call separated methods for displaying each type of field
					$input_html .= call_user_func( array( $field_class, 'html' ), $sub_meta, $sub_field );

					// Apply filter to field HTML
					// 1st filter applies to all fields with the same type
					// 2nd filter applies to current field only
					$input_html = apply_filters( "rwmb_{$type}_html", $input_html, $field, $sub_meta );
					$input_html = apply_filters( "rwmb_{$id}_html", $input_html, $field, $sub_meta );

					// Remove clone button
					$input_html .= call_user_func( array( $field_class, 'remove_clone_button' ), $sub_field );

					$input_html .= '</div>';

					$field_html .= $input_html;
				}
			}
			// Non-cloneable fields
			else
			{
				// Call separated methods for displaying each type of field
				$field_html = call_user_func( array( $field_class, 'html' ), $meta, $field );

				// Apply filter to field HTML
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$field_html = apply_filters( "rwmb_{$type}_html", $field_html, $field, $meta );
				$field_html = apply_filters( "rwmb_{$id}_html", $field_html, $field, $meta );
			}

			$end = call_user_func( array( $field_class, 'end_html' ), $meta, $field );

			// Apply filter to field end HTML
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$end = apply_filters( 'rwmb_end_html', $end, $field, $meta );
			$end = apply_filters( "rwmb_{$type}_end_html", $end, $field, $meta );
			$end = apply_filters( "rwmb_{$id}_end_html", $end, $field, $meta );

			// Apply filter to field wrapper
			// This allow users to change whole HTML markup of the field wrapper (i.e. table row)
			// 1st filter applies to all fields
			// 1st filter applies to all fields with the same type
			// 2nd filter applies to current field only
			$html = apply_filters( 'rwmb_wrapper_html', "{$begin}{$field_html}{$end}", $field, $meta );
			$html = apply_filters( "rwmb_{$type}_wrapper_html", $html, $field, $meta );
			$html = apply_filters( "rwmb_{$id}_wrapper_html", $html, $field, $meta );

			// Display label and input in DIV and allow user-defined classes to be appended
			$classes = array( 'rwmb-field', "rwmb-{$type}-wrapper" );
			if ( 'hidden' === $field['type'] )
				$classes[] = 'hidden';
			if ( ! empty( $field['required'] ) )
				$classes[] = 'required';
			if ( ! empty( $field['class'] ) )
				$classes[] = $field['class'];

			$outer_html = sprintf(
				$field['before'] . '<div class="%s">%s</div>' . $field['after'],
				implode( ' ', $classes ),
				$html
			);

			// Allow to change output of outer div
			// 1st filter applies to all fields
			// 1st filter applies to all fields with the same type
			// 2nd filter applies to current field only
			$outer_html = apply_filters( 'rwmb_outer_html', $outer_html, $field, $meta );
			$outer_html = apply_filters( "rwmb_{$type}_outer_html", $outer_html, $field, $meta );
			$outer_html = apply_filters( "rwmb_{$id}_outer_html", $outer_html, $field, $meta );

			echo $outer_html;
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			return '';
		}

		/**
		 * Show begin HTML markup for fields
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function begin_html( $meta, $field )
		{
			$field_label = '';
			if ( $field['name'] )
			{
				$field_label = sprintf(
					'<div class="rwmb-label"><label for="%s">%s</label></div>',
					$field['id'],
					$field['name']
				);
			}

			$data_max_clone = '';
			if ( is_numeric( $field['max_clone'] ) && $field['max_clone'] > 1 )
			{
				$data_max_clone .= ' data-max-clone=' . $field['max_clone'];
			}

			$input_open = sprintf(
				'<div class="rwmb-input"%s>',
				$data_max_clone
			);

			return $field_label . $input_open;
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			$button = $field['clone'] ? call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'add_clone_button' ), $field ) : '';
			$desc   = $field['desc'] ? "<p id='{$field['id']}_description' class='description'>{$field['desc']}</p>" : '';

			// Closes the container
			$html = "{$button}{$desc}</div>";

			return $html;
		}

		/**
		 * Add clone button
		 *
		 * @param array $field Field parameter
		 *
		 * @return string $html
		 */
		static function add_clone_button( $field )
		{
			$text = apply_filters( 'rwmb_add_clone_button_text', __( '+ Add more', 'meta-box' ), $field );
			return "<a href='#' class='rwmb-button button-primary add-clone'>$text</a>";
		}

		/**
		 * Remove clone button
		 *
		 * @param array $field Field parameter
		 *
		 * @return string $html
		 */
		static function remove_clone_button( $field )
		{
			$icon = '<i class="dashicons dashicons-minus"></i>';
			$text = apply_filters( 'rwmb_remove_clone_button_text', $icon, $field );
			return "<a href='#' class='rwmb-button remove-clone'>$text</a>";
		}

		/**
		 * Get meta value
		 *
		 * @param int   $post_id
		 * @param bool  $saved
		 * @param array $field
		 *
		 * @return mixed
		 */
		static function meta( $post_id, $saved, $field )
		{
			/**
			 * For special fields like 'divider', 'heading' which don't have ID, just return empty string
			 * to prevent notice error when displaying fields
			 */
			if ( empty( $field['id'] ) )
				return '';

			$single = $field['clone'] || ! $field['multiple'];
			$meta   = get_post_meta( $post_id, $field['id'], $single );

			// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run)
			$meta = ( ! $saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;

			// Escape attributes
			$meta = call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'esc_meta' ), $meta );

			// Make sure meta value is an array for clonable and multiple fields
			if ( $field['clone'] || $field['multiple'] )
			{
				if ( empty( $meta ) || ! is_array( $meta ) )
				{
					/**
					 * Note: if field is clonable, $meta must be an array with values
					 * so that the foreach loop in self::show() runs properly
					 * @see self::show()
					 */
					$meta = $field['clone'] ? array( '' ) : array();
				}
			}

			return $meta;
		}

		/**
		 * Escape meta for field output
		 *
		 * @param mixed $meta
		 *
		 * @return mixed
		 */
		static function esc_meta( $meta )
		{
			return is_array( $meta ) ? array_map( __METHOD__, $meta ) : esc_attr( $meta );
		}

		/**
		 * Set value of meta before saving into database
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return int
		 */
		static function value( $new, $old, $post_id, $field )
		{
			return $new;
		}

		/**
		 * Save meta value
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$name = $field['id'];

			// Remove post meta if it's empty
			if ( '' === $new || array() === $new )
			{
				delete_post_meta( $post_id, $name );

				return;
			}

			// If field is cloneable, value is saved as a single entry in the database
			if ( $field['clone'] )
			{
				$new = (array) $new;
				foreach ( $new as $k => $v )
				{
					if ( '' === $v )
						unset( $new[$k] );
				}
				update_post_meta( $post_id, $name, $new );
				return;
			}

			// If field is multiple, value is saved as multiple entries in the database (WordPress behaviour)
			if ( $field['multiple'] )
			{
				foreach ( $new as $new_value )
				{
					if ( ! in_array( $new_value, $old ) )
						add_post_meta( $post_id, $name, $new_value, false );
				}
				foreach ( $old as $old_value )
				{
					if ( ! in_array( $old_value, $new ) )
						delete_post_meta( $post_id, $name, $old_value );
				}
				return;
			}

			// Default: just update post meta
			update_post_meta( $post_id, $name, $new );
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'disabled'   => false,
				'required'   => false,
				'attributes' => array()
			) );

			$field['attributes'] = wp_parse_args( $field['attributes'], array(
				'disabled' => $field['disabled'],
				'required' => $field['required'],
				'class'    => "rwmb-{$field['type']}",
				'id'       => $field['clone'] ? false : $field['id'],
				'name'     => $field['field_name'],
			) );

			return $field;
		}

		/**
		 * Renders an attribute array into an html attributes string
		 *
		 * @param array $attributes
		 *
		 * @return string
		 */
		static function render_attributes( $attributes )
		{
			$attr_string = '';
			foreach ( $attributes as $key => $value )
			{
				if ( $value )
				{
					$value = ( true === $value ) ? $key : $value;
					$attr_string .= sprintf(
						' %s="%s"',
						$key,
						esc_attr( $value )
					);
				}
			}
			return $attr_string;
		}

		/**
		 * Get the field value
		 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
		 * of the field saved in the database, while this function returns more meaningful value of the field, for ex.:
		 * for file/image: return array of file/image information instead of file/image IDs
		 *
		 * Each field can extend this function and add more data to the returned value.
		 * See specific field classes for details.
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function get_value( $field, $args = array(), $post_id = null )
		{
			if ( ! $post_id )
				$post_id = get_the_ID();

			/**
			 * Get raw meta value in the database, no escape
			 * Very similar to self::meta() function
			 */

			/**
			 * For special fields like 'divider', 'heading' which don't have ID, just return empty string
			 * to prevent notice error when display in fields
			 */
			$value = '';
			if ( ! empty( $field['id'] ) )
			{
				$single = $field['clone'] || ! $field['multiple'];
				$value  = get_post_meta( $post_id, $field['id'], $single );

				// Make sure meta value is an array for clonable and multiple fields
				if ( $field['clone'] || $field['multiple'] )
				{
					$value = is_array( $value ) && $value ? $value : array();
				}
			}

			/**
			 * Return the meta value by default.
			 * For specific fields, the returned value might be different. See each field class for details
			 */
			return $value;
		}

		/**
		 * Output the field value
		 * Depends on field value and field types, each field can extend this method to output its value in its own way
		 * See specific field classes for details.
		 *
		 * Note: we don't echo the field value directly. We return the output HTML of field, which will be used in
		 * rwmb_the_field function later.
		 *
		 * @use self::get_value()
		 * @see rwmb_the_field()
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return string HTML output of the field
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value  = call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'get_value' ), $field, $args, $post_id );
			$output = $value;
			if ( is_array( $value ) )
			{
				$output = '<ul>';
				foreach ( $value as $subvalue )
				{
					$output .= '<li>' . $subvalue . '</li>';
				}
				$output .= '</ul>';
			}
			return $output;
		}
	}
}
