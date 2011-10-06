<?php

if ( !class_exists( 'RW_Meta_Box_Radio_Field' ) ) {

	class RW_Meta_Box_Radio_Field {

		/**
		 * Show HTML markup for radio field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			$html = '';
			foreach ( $field['options'] as $key => $value ) {
				$html .= "<input type='radio' class='rw-radio' name='{$field['id']}' value='$key'" . checked( $meta, $key, false ) . " /> $value ";
			}

			return $html;
		}
	}
}