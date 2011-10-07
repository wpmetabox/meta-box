<?php

if ( !class_exists( 'RWMB_Checkbox_List_Field' ) ) {

	class RWMB_Checkbox_List_Field {

		/**
		 * Get field HTML
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			if ( !is_array( $meta ) )
				$meta = (array) $meta;
			$html = array( );
			foreach ( $field['options'] as $key => $value ) {
				$html[] = "<input type='checkbox' class='rwmb-checkbox-list' name='{$field['id']}[]' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> $value";
			}
			return implode( '<br />', $html );
		}
	}
}