<?php

if ( !class_exists( 'RWMB_Text_Field' ) ) {

	class RWMB_Text_Field {

		/**
		 * Get field end HTML
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<input type='text' class='rwmb-text' name='{$field['id']}' id='{$field['id']}' value='$meta' size='30' />";
		}
	}
}