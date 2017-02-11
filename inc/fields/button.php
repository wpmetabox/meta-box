<?php
/**
 * The button field. Simply displays a HTML button which might be used for JavaScript actions.
 *
 * @package Meta Box
 */

/**
 * Button field class.
 */
class RWMB_Button_Field extends RWMB_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field The field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field );
		return sprintf( '<a href="#" %s>%s</a>', self::render_attributes( $attributes ), $field['std'] );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field The field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field        = parent::normalize( $field );
		$field['std'] = $field['std'] ? $field['std'] : __( 'Click me', 'meta-box' );
		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The attribute value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes['class'] .= ' button hide-if-no-js';

		return $attributes;
	}
}
