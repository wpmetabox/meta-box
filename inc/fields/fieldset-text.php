<?php

/**
 * Fieldset text class.
 */
class RWMB_Fieldset_Text_Field extends RWMB_Text_Field
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
		$html = array();
		$tpl  = '<label>%s %s</label>';

		foreach ( $field['options'] as $key => $label )
		{
			$value                       = isset( $meta[$key] ) ? $meta[$key] : '';
			$field['attributes']['name'] = $field['field_name'] . "[{$key}]";
			$html[]                      = sprintf( $tpl, $label, parent::html( $value, $field ) );
		}

		$out = '<fieldset><legend>' . $field['desc'] . '</legend>' . implode( ' ', $html ) . '</fieldset>';

		return $out;
	}

	/**
	 * Show end HTML markup for fields
	 * Do not show field description. Field description is shown before list of fields
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	static function end_html( $meta, $field )
	{
		$button = $field['clone'] ? self::add_clone_button( $field ) : '';
		$html   = "$button</div>";
		return $html;
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
		$field                       = parent::normalize( $field );
		$field['multiple']           = false;
		$field['attributes']['id']   = false;
		$field['attributes']['type'] = 'text';
		return $field;
	}

	/**
	 * Format value for the helper functions.
	 * @param array        $field Field parameter
	 * @param string|array $value The field meta value
	 * @return string
	 */
	public static function format_value( $field, $value )
	{
		$output = '<table><thead><tr>';
		foreach ( $field['options'] as $label )
		{
			$output .= "<th>$label</th>";
		}
		$output .= '<tr>';

		if ( ! $field['clone'] )
		{
			$output .= self::format_single_value( $field, $value );
		}
		else
		{
			foreach ( $value as $subvalue )
			{
				$output .= self::format_single_value( $field, $subvalue );
			}
		}
		$output .= '</tbody></table>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions.
	 * @param array $field Field parameter
	 * @param array $value The value
	 * @return string
	 */
	public static function format_single_value( $field, $value )
	{
		$output = '<tr>';
		foreach ( $value as $subvalue )
		{
			$output .= "<td>$subvalue</td>";
		}
		$output .= '</tr>';
		return $output;
	}
}
