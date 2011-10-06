<?php

if ( !class_exists( 'RW_Meta_Box_Select_Field' ) ) {

	class RW_Meta_Box_Select_Field {

		/**
		 * Enqueue scripts and styles for select field
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'rw-meta-box-select', RW_META_BOX_CSS . 'select.css', RW_META_BOX_VER );
		}

		/**
		 * Show HTML markup for select field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			if ( !is_array( $meta ) )
				$meta = (array) $meta;
			$html = "<select class='rw-select' name='{$field['id']}" . ( $field['multiple'] ? "[]' id='{$field['id']}' multiple='multiple'" : "'" ) . ">";
			foreach ( $field['options'] as $key => $value ) {
				$html .= "<option value='$key'" . selected( in_array( $key, $meta ), true, false ) . ">$value</option>";
			}
			$html .= "</select>";

			return $html;
		}
	}
}