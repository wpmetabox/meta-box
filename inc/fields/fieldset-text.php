<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Fieldset_Text_Field' ) )
{
	class RWMB_Fieldset_Text_Field extends RWMB_Field
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
			$html = array();
			$tpl  = '<label>%s <input type="text" class="rwmb-fieldset-text" name="%s[%d][%s]" value="%s"></label>';

			for ( $row = 0; $row < $field['rows']; $row ++ )
			{
				foreach ( $field['options'] as $key => $label )
				{
					$value  = isset( $meta[$row][$key] ) ? $meta[$row][$key] : '';
					$html[] = sprintf( $tpl, $label, $field['id'], $row, $key, $value );
				}
				$html[] = '<br>';
			}

			$out = '<fieldset><legend>' . $field['desc'] . '</legend>' . implode( ' ', $html ) . '</fieldset>';

			return $out;
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
			foreach ( $meta as &$pairs )
			{
				$pairs = array_map( 'esc_attr', $pairs );
			}
			return $meta;
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
			$field['multiple'] = false;
			return $field;
		}

	}
}
