<?php

if ( !class_exists( 'RW_Meta_Box_Checkbox_List_Field' ) ) {

	class RW_Meta_Box_Checkbox_List_Field {

		/**
		 * Show HTML markup for checkbox list field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			if ( !is_array( $meta ) )
				$meta = (array) $meta;
			$html = array( );
			foreach ( $field['options'] as $key => $value ) {
				$html[] = "<input type='checkbox' class='rw-checkbox_list' name='{$field['id']}[]' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> $value";
			}
			return implode( '<br />', $html );
		}
	}
}