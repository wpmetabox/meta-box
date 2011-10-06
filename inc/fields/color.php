<?php

if ( !class_exists( 'RW_Meta_Box_Color_Field' ) ) {

	class RW_Meta_Box_Color_Field {

		/**
		 * Enqueue scripts and styles for color field
		 */
		static function admin_print_styles( ) {
			wp_enqueue_style( 'farbtastic' );

			wp_enqueue_script( 'rw-meta-box-color', RW_META_BOX_JS . 'color.js', array( 'farbtastic' ), RW_META_BOX_VER, true );
		}

		/**
		 * Show HTML markup for color field
		 * @param $field
		 * @param $meta
		 * @return string
		 */
		static function html( $field, $meta ) {
			if ( empty( $meta ) )
				$meta = '#';
			$html = <<<HTML
<input class="rw-color" type="text" name="{$field['id']}" id="{$field['id']}" value="$meta" size="8" />
<a href="#" class="rw-color-select" rel="{$field['id']}">%s</a>
<div style="display: none" class="rw-color-picker" rel="{$field['id']}"></div>
HTML;
			$html = sprintf( $html, __( 'Select a color' ) );
			return $html;
		}
	}
}