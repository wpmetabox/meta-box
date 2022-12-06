<?php
/**
 * The custom HTML field which allows users to output any kind of content to the meta box.
 */
class RWMB_Custom_Html_Field extends RWMB_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html = ! empty( $field['std'] ) ? $field['std'] : '';
		if ( ! empty( $field['callback'] ) && is_callable( $field['callback'] ) ) {
			$html = call_user_func_array( $field['callback'], [ $meta, $field ] );
		}
		return $html;
	}
}
