<?php
/**
 * The divider field which displays a simple horizontal line.
 */
class RWMB_Divider_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-divider', RWMB_CSS_URL . 'divider.css', [], RWMB_VER );
	}

	protected static function begin_html( array $field ) : string {
		$attributes = empty( $field['id'] ) ? '' : " id='{$field['id']}'";
		return "<hr$attributes>";
	}

	public static function end_html( array $field ) : string {
		return '';
	}
}
