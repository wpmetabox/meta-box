<?php

/**
 * Text list field class.
 */
class RWMB_Text_List_Field extends RWMB_Multiple_Values_Field
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
		$html  = array();
		$input = '<label><input type="text" class="rwmb-text-list" name="%s" value="%s" placeholder="%s"> %s</label>';

		$count = 0;
		foreach ( $field['options'] as $placeholder => $label )
		{
			$html[] = sprintf(
				$input,
				$field['field_name'],
				isset( $meta[$count] ) ? esc_attr( $meta[$count] ) : '',
				$placeholder,
				$label
			);
			$count ++;
		}

		return implode( ' ', $html );
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
