<?php
/**
 * Checkbox list field class.
 */
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

		foreach ( $field['options'] as $value => $label )
		{
			$attributes = self::get_attributes( $field, $value );
			$html[]     = sprintf(
				$tpl,
				self::render_attributes( $attributes ),
				checked( in_array( (string) $value, $meta, true ), 1, false ),
				$label
			);
		}

		return implode( '<br>', $html );
	}

	/**
	 * Normalize parameters for field
	 * @param array $field
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = RWMB_Checkbox_Field::normalize( $field );

		return $field;
	}

	/**
	 * Get the attributes for field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes       = RWMB_Checkbox_Field::get_attributes( $field, $value );
		$attributes['id'] = false;

		return $attributes;
	}
}
