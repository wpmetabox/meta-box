<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Checkbox_List_Field' ) )
{
	class RWMB_Checkbox_List_Field extends RWMB_Field
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
			$meta = (array) $meta;
			$html = array();
			$tpl  = '<label><input type="checkbox" class="rwmb-checkbox-list" name="%s" value="%s"%s> %s</label>';

			foreach ( $field['options'] as $value => $label )
			{
				$html[] = sprintf(
					$tpl,
					$field['field_name'],
					$value,
					checked( in_array( $value, $meta ), 1, false ),
					$label
				);
			}

			return implode( '<br>', $html );
		}

		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'], $field['clone'] );
			$meta = ( ! $saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;
			$meta = array_map( 'esc_attr', (array) $meta );

			return $meta;
		}

		/**
		 * Save meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			if ( ! $field['clone'] )
			{
				parent::save( $new, $old, $post_id, $field );

				return;
			}

			if ( empty( $new ) )
				delete_post_meta( $post_id, $field['id'] );
			else
				update_post_meta( $post_id, $field['id'], $new );
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
			$field['multiple']   = true;
			$field['field_name'] = $field['id'];
			if ( ! $field['clone'] )
				$field['field_name'] .= '[]';

			return $field;
		}

		/**
		 * Output the field value
		 * Display option name instead of option value
		 *
		 * @use self::meta()
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = parent::get_value( $field, $args, $post_id );
			if ( ! $value )
				return '';

			$output = '<ul>';
			if ( $field['clone'] )
			{
				foreach ( $value as $subvalue )
				{
					$output .= '<li>';
					$output .= '<ul>';
					foreach ( $subvalue as $option )
					{
						$output .= '<li>' . $field['options'][$option] . '</li>';
					}
					$output .= '</ul>';
					$output .= '</li>';
				}
			}
			else
			{
				foreach ( $value as $option )
				{
					$output .= '<li>' . $field['options'][$option] . '</li>';
				}
			}
			$output .= '</ul>';

			return $output;
		}
	}
}
