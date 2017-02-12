<?php
/**
 * The checkbox list field which shows a list of choices and allow users to select multiple options.
 *
 * @package Meta Box
 */

/**
 * Checkbox list field class.
 */
class RWMB_Checkbox_List_Field extends RWMB_Input_List_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field['multiple'] = true;
		$field = parent::normalize( $field );

		return $field;
	}
}
