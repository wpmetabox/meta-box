<?php

if ( !class_exists( 'RW_Meta_Box_Checkbox_Field' ) ) {

	class RW_Meta_Box_Checkbox_Field {

		/**
		 * Show HTML markup for checkbox field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<input type='checkbox' class='rw-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked( !empty( $meta ), true, false ) . " />";
		}
	}
}