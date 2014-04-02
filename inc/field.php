<?php
if ( !class_exists( 'RWMB_Field ' ) )
{
	class RWMB_Field
	{
		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions() {}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts() {}

		/**
		 * Show field HTML
		 *
		 * @param array $field
		 * @param bool  $saved
		 *
		 * @return string
		 */
		static function show( $field, $saved )
		{
			global $post;

			$field_class = RW_Meta_Box::get_class_name( $field );
			$meta = call_user_func( array( $field_class, 'meta' ), $post->ID, $saved, $field );

			$group = '';	// Empty the clone-group field
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
				if ( isset( $field['clone-group'] ) )
					$group = " clone-group='{$field['clone-group']}'";

				$meta = (array) $meta;

				$field_html = '';

				foreach ( $meta as $index => $sub_meta )
				{
					$sub_field = $field;
					$sub_field['field_name'] = $field['field_name'] . "[{$index}]";
					if ($index>0) {
						if (isset( $sub_field['address_field'] )) 
							$sub_field['address_field'] = $field['address_field'] . "_{$index}";
						$sub_field['id'] = $field['id'] . "_{$index}";
					}
					if ( $field['multiple'] )
						$sub_field['field_name'] .= '[]';

					// Wrap field HTML in a div with class="rwmb-clone" if needed
					$input_html = '<div class="rwmb-clone">';

					// Call separated methods for displaying each type of field
					$input_html .= call_user_func( array( $field_class, 'html' ), $sub_meta, $sub_field );

					// Apply filter to field HTML
					// 1st filter applies to all fields with the same type
					// 2nd filter applies to current field only
					$input_html = apply_filters( "rwmb_{$type}_html", $input_html, $field, $sub_meta );
					$input_html = apply_filters( "rwmb_{$id}_html", $input_html, $field, $sub_meta );

					// Add clone button
					$input_html .= self::clone_button();

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
			// 1st filter applies to all fields with the same type
			// 2nd filter applies to current field only
			$html = apply_filters( "rwmb_{$type}_wrapper_html", "{$begin}{$field_html}{$end}", $field, $meta );
			$html = apply_filters( "rwmb_{$id}_wrapper_html", $html, $field, $meta );

			// Display label and input in DIV and allow user-defined classes to be appended
			$classes = array( 'rwmb-field', "rwmb-{$type}-wrapper" );
			if ( 'hidden' === $field['type'] )
				$classes[] = 'hidden';
			if ( !empty( $field['required'] ) )
				$classes[] = 'required';
			if ( !empty( $field['class'] ) )
				$classes[] = $field['class'];

			printf(
				$field['before'] . '<div class="%s"%s>%s</div>' . $field['after'],
				implode( ' ', $classes ),
				$group,
				$html
			);
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
			if ( empty( $field['name'] ) )
				return '<div class="rwmb-input">';

			return sprintf(
				'<div class="rwmb-label">
					<label for="%s">%s</label>
				</div>
				<div class="rwmb-input">',
				$field['id'],
				$field['name']
			);
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
			$id = $field['id'];

			$button = '';
			if ( $field['clone'] )
				$button = '<a href="#" class="rwmb-button button-primary add-clone">' . __( '+', 'rwmb' ) . '</a>';

			$desc = !empty( $field['desc'] ) ? "<p id='{$id}_description' class='description'>{$field['desc']}</p>" : '';

			// Closes the container
			$html = "{$button}{$desc}</div>";

			return $html;
		}

		/**
		 * Add clone button
		 *
		 * @return string $html
		 */
		static function clone_button()
		{
			return '<a href="#" class="rwmb-button button remove-clone">' . __( '&#8211;', 'rwmb' ) . '</a>';
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
			$meta = get_post_meta( $post_id, $field['id'], !$field['multiple'] );

			// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run)
			$meta = ( !$saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;

			// Escape attributes for non-wysiwyg fields
			if ( 'wysiwyg' !== $field['type'] )
				$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

			$meta = apply_filters( "rwmb_{$field['type']}_meta", $meta );
			$meta = apply_filters( "rwmb_{$field['id']}_meta", $meta );

			return $meta;
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

			if ( '' === $new || array() === $new )
			{
				delete_post_meta( $post_id, $name );
				return;
			}

			if ( $field['multiple'] )
			{
				foreach ( $new as $new_value )
				{
					if ( !in_array( $new_value, $old ) )
						add_post_meta( $post_id, $name, $new_value, false );
				}
				foreach ( $old as $old_value )
				{
					if ( !in_array( $old_value, $new ) )
						delete_post_meta( $post_id, $name, $old_value );
				}
			}
			else
			{
				if ( $field['clone'] )
				{
					$new = (array) $new;
					foreach ( $new as $k => $v )
					{
						if ( '' === $v )
							unset( $new[$k] );
					}
				}
				update_post_meta( $post_id, $name, $new );
			}
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
			return $field;
		}
	}
}