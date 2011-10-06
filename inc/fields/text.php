<?php

if ( !class_exists( 'RW_Meta_Box_Text_Field' ) ) {

	class RW_Meta_Box_Text_Field {

		/**
		 * Show HTML markup for text field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<input type='text' class='rw-text' name='{$field['id']}' id='{$field['id']}' value='$meta' size='30' />";
		}
	}
}