<?php

if ( !class_exists( 'RWMB_Text_Field' ) ) {

	class RWMB_Text_Field {

		/**
		 * Get field end HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			return "<input type='text' class='rwmb-text' name='{$field['id']}' id='{$field['id']}' value='$meta' size='30' />";
		}
	}
}