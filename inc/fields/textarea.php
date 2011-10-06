<?php

if ( !class_exists( 'RW_Meta_Box_Textarea_Field' ) ) {

	class RW_Meta_Box_Textarea_Field {

		/**
		 * Show HTML markup for text field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<textarea class='rw-textarea large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
		}
	}
}