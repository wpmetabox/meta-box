<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Field_Multiple_Values' ) )
{
	/**
	 * This class implements common methods used in fields which have multiple values
	 * like checkbox list, autocomplete, etc.
	 *
	 * The difference when handling actions for these fields are the way they get/set
	 * meta value. Briefly:
	 * - If field is cloneable, value is saved as a single entry in the database
	 * - Otherwise value is saved as multiple entries
	 */
	class RWMB_Field_Multiple_Values extends RWMB_Field
	{
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
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = self::get_value( $field, $args, $post_id );
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
