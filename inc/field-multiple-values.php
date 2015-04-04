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
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries
		 *
		 * @see "save" method for better understanding
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
			if ( ! is_array( $meta ) )
			{
				$meta = array();
			}

			// Escape values
			if ( $field['clone'] )
			{
				foreach ( $meta as &$submeta )
				{
					$submeta = array_map( 'esc_attr', $submeta );
				}
			}
			else
			{
				$meta = array_map( 'esc_attr', $meta );
			}

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
		 * Get the field value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
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
			$value = get_post_meta( $post_id, $field['id'], $field['clone'] );
			if ( ! is_array( $value ) )
			{
				$value = array();
			}

			return $value;
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
