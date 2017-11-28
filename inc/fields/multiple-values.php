<?php
/**
 * This class implements common methods used in fields which have multiple values
 * like checkbox list, autocomplete, etc.
 *
 * The difference when handling actions for these fields are the way they get/set
 * meta value. Briefly:
 * - If field is cloneable, value is saved as a single entry in the database
 * - Otherwise value is saved as multiple entries
 *
 * @package Meta Box
 */

/**
 * Multiple values field class.
 */
abstract class RWMB_Multiple_Values_Field extends RWMB_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field               = parent::normalize( $field );
		$field['multiple']   = true;
		$field['field_name'] = $field['id'];
		if ( ! $field['clone'] ) {
			$field['field_name'] .= '[]';
		}

		return $field;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return $field['options'][ $value ];
	}
}
