<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Key_Value_Field' ) )
{
	class RWMB_Key_Value_Field extends RWMB_Field
	{
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
			$tpl = '<input type="text" class="rwmb-key-val" name="%s[]" value="%s" placeholder="' . esc_attr__( 'Key', 'meta-box' ) . '">';
			$tpl .= '<input type="text" class="rwmb-key-val" name="%s[]" value="%s" placeholder="' . esc_attr__( 'Value', 'meta-box' ) . '">';

			$key = isset( $meta[0] ) ? $meta[0] : '';
			$val = isset( $meta[1] ) ? $meta[1] : '';

			$html = sprintf( $tpl, $field['field_name'], $key, $field['field_name'], $val );

			return $html;
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
			$desc = $field['desc'] ? "<p id='{$field['id']}_description' class='description'>{$field['desc']}</p>" : '';

			if ( empty( $field['name'] ) )
				return '<div class="rwmb-input">' . $desc;

			return sprintf(
				'<div class="rwmb-label">
					<label for="%s">%s</label>
				</div>
				<div class="rwmb-input">
				%s',
				$field['id'],
				$field['name'],
				$desc
			);
		}

		/**
		 * Show end HTML markup for fields
		 * Do not show field description. Field description is shown before list of fields
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			$button = $field['clone'] ? call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'add_clone_button' ), $field ) : '';

			// Closes the container
			$html = "$button</div>";

			return $html;
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
			foreach ( (array) $meta as $k => $pairs )
			{
				$meta[$k] = array_map( 'esc_attr', (array) $pairs );
			}
			return $meta;
		}

		/**
		 * Sanitize email
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field )
		{
			foreach ( $new as &$arr )
			{
				if ( empty( $arr[0] ) && empty( $arr[1] ) )
					$arr = false;
			}

			$new = array_filter( $new );

			return $new;
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
			$field['clone']    = true;
			$field['multiple'] = false;

			return $field;
		}

		/**
		 * Output the field value
		 * Display unordered list of key - value pairs
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
			$value = self::get_value( $field, $args, $post_id );
			if ( ! is_array( $value ) )
				return '';

			$output = '<ul>';
			foreach ( $value as $subvalue )
			{
				$output .= sprintf( '<li><label>%s</label>: %s</li>', $subvalue[0], $subvalue[1] );
			}
			$output .= '</ul>';
			return $output;
		}
	}
}
