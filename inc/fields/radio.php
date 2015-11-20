<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "input" field is loaded
require_once RWMB_FIELDS_DIR . 'input.php';

if ( ! class_exists( 'RWMB_Radio_Field' ) )
{
	class RWMB_Radio_Field extends RWMB_Input_Field
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
			$html       = array();
			$tpl        = '<label><input %s %s> %s</label>';
			$attributes = $field['attributes'];

			foreach ( $field['options'] as $value => $label )
			{
				$attributes['value'] = $value;
				$html[]              = sprintf(
					$tpl,
					self::render_attributes( $attributes ),
					checked( $value, $meta, false ),
					$label
				);
			}

			return implode( ' ', $html );
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
			$field = parent::normalize_field( $field );

			$field['attributes']['list'] = false;
			$field['attributes']['id']   = false;
			$field['attributes']['type'] = 'radio';

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

			return empty( $value ) ? '' : $field['options'][$value];
		}
	}
}
