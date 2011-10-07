<?php

if ( !class_exists( 'RWMB_Textarea_Field' ) ) {

	class RWMB_Textarea_Field {

		/**
		 * Get field end HTML
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			return "<textarea class='rwmb-textarea large-text' name='{$field['id']}' id='{$field['id']}' cols='60' rows='10'>$meta</textarea>";
		}
	}
}