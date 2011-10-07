<?php

if ( !class_exists( 'RWMB_Radio_Field' ) ) {

	class RWMB_Radio_Field {

		/**
		 * Get field end HTML
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			$html = '';
			foreach ( $field['options'] as $key => $value ) {
				$html .= "<input type='radio' class='rwmb-radio' name='{$field['id']}' value='$key'" . checked( $meta, $key, false ) . " /> $value ";
			}

			return $html;
		}
	}
}