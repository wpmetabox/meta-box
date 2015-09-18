<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Radio_Field' ) )
{
	class RWMB_Radio_Field extends RWMB_Field
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
			$tpl  = '<label><input type="radio" class="rwmb-radio" name="%s" value="%s"%s> %s</label>';

			foreach ( $field['options'] as $value => $label )
			{
				$html[] = sprintf(
					$tpl,
					$field['field_name'],
					$value,
					checked( $value, $meta, false ),
					$label
				);
			}

			return implode( ' ', $html );
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
