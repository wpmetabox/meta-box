<?php
defined( 'ABSPATH' ) || die;

/**
 * The radio field.
 */
class RWMB_Radio_Field extends RWMB_Input_List_Field {
	public static function normalize( $field ) {
		$field['multiple'] = false;
		$field             = parent::normalize( $field );

		return $field;
	}
}
