<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Checkbox_List_Field extends RWMB_Multiple_Values_Field
{
	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$meta = (array) $meta;
		$html = array();
		$tpl  = '<label><input %s %s> %s</label>';
		$field_class = RW_Meta_Box::get_class_name( $field );

		foreach ( $field['options'] as $value => $label )
		{
			$attributes = call_user_func( array( $field_class, 'get_attributes' ), $field, $value );
			$html[] = sprintf(
				$tpl,
				self::render_attributes( $attributes ),
				checked( in_array( $value, $meta ), 1, false ),
				$label
			);
		}

		return implode( '<br>', $html );
	}
	
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		return RWMB_Checkbox_Field::normalize( $field );
	}
	
	static function get_attributes( $field, $value = null )
	{
		$attributes = RWMB_Checkbox_Field::get_attributes( $field, $value );
		$attributes['id'] 	 = false;
		
		return $attributes;
	}
}
