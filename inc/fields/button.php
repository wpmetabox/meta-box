<?php
/**
 * Button field class.
 */
class RWMB_Button_Field extends RWMB_Field
{
	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$attributes = self::get_attributes( $field );
		return sprintf( '<a href="#" %s>%s</a>', self::render_attributes( $attributes ), $field['std'] );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field        = parent::normalize( $field );
		$field['std'] = $field['std'] ? $field['std'] : __( 'Click me', 'meta-box' );
		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['class'] .= ' button hide-if-no-js';

		return $attributes;
	}
}
