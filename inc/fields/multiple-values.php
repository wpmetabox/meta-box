<?php
/**
 * This class implements common methods used in fields which have multiple values
 * like checkbox list, autocomplete, etc.
 *
 * The difference when handling actions for these fields are the way they get/set
 * meta value. Briefly:
 * - If field is cloneable, value is saved as a single entry in the database
 * - Otherwise value is saved as multiple entries
 */
abstract class RWMB_Multiple_Values_Field extends RWMB_Field
{
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field               = parent::normalize( $field );
		$field['multiple']   = true;
		$field['field_name'] = $field['id'];
		if ( ! $field['clone'] )
			$field['field_name'] .= '[]';

		return $field;
	}

	/**
	 * Format a single value for the helper functions.
	 * @param array  $field Field parameter
	 * @param string $value The value
	 * @return string
	 */
	static function format_single_value( $field, $value )
	{
		return $field['options'][$value];
	}
}
