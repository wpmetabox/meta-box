<?php

if ( !class_exists( 'RWMB_Select_Field' ) ) {

	class RWMB_Select_Field {

		/**
		 * Enqueue scripts and styles
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', RWMB_VER );
		}

		/**
		 * Get field end HTML
		 * @param $html
		 * @param $meta
		 * @param $field
		 * @return string
		 */
		static function html( $html, $meta, $field ) {
			if ( !is_array( $meta ) )
				$meta = (array) $meta;
			$html = "<select class='rwmb-select' name='{$field['id']}" . ( $field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'" ) . ">";
			foreach ( $field['options'] as $key => $value ) {
				$html .= "<option value='$key'" . selected( in_array( $key, $meta ), true, false ) . ">$value</option>";
			}
			$html .= "</select>";

			return $html;
		}
	}
}