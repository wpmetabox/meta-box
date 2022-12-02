<?php
/**
 * The divider field which displays a simple horizontal line.
 */
class RWMB_Divider_Field extends RWMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-divider', RWMB_CSS_URL . 'divider.css', [], RWMB_VER );
	}

	/**
	 * Show begin HTML markup for fields.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function begin_html( $meta, $field ) {
		$attributes = empty( $field['id'] ) ? '' : " id='{$field['id']}'";
		return "<hr$attributes>";
	}

	/**
	 * Show end HTML markup for fields.
	 */
	public static function end_html( array $field ) : string {
		return '';
	}
}
