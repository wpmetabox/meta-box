<?php

if ( !class_exists( 'RWMB_Radio_Field' ) ) {

	class RWMB_Radio_Field {

		/**
		 * Get field end HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			$html = '';
			foreach ( $field['options'] as $key => $value ) {
				$html .= "<input type='radio' class='rwmb-radio' name='{$field['id']}' value='$key'" . checked( $meta, $key, false ) . " /> $value ";
			}

			return $html;
		}
	}
}