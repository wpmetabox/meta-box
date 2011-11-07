<?php

if ( !class_exists( 'RWMB_Checkbox_Field' ) ) {

	class RWMB_Checkbox_Field {

		/**
		 * Additional actions for checkbox field
		 */
		static function add_actions( ) {
			/**
			 * Change wrapper HTML
			 * Use priority = 1 to allow other scripts change this value
			 */
			add_filter( "rwmb_checkbox_end_html", array( __CLASS__, 'end_html' ), 1, 3 );
		}

		/**
		 * Get field end HTML
		 * @param $end_html
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function end_html( $end_html, $field, $meta ) {
			return " <span class='description'>{$field['desc']}</span>";
		}

		/**
		 * Get field HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			return "<input type='checkbox' class='rwmb-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked( !empty( $meta ), true, false ) . " />";
		}
	}
}