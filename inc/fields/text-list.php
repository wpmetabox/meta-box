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
	 * Output the field value
	 * Display option name instead of option value
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = self::get_value( $field, $args, $post_id );
		if ( ! $value )
			return '';

		$output = '<ul>';
		if ( $field['clone'] )
		{
			foreach ( $value as $subvalue )
			{
				$output .= '<li>';
				$output .= '<ul>';

				$i = 0;
				foreach ( $field['options'] as $placeholder => $label )
				{
					$output .= sprintf(
						'<li><label>%s</label>: %s</li>',
						$label,
						isset( $subvalue[$i] ) ? $subvalue[$i] : ''
					);
					$i ++;
				}
				$output .= '</ul>';
				$output .= '</li>';
			}
		}
		else
		{
			$i = 0;
			foreach ( $field['options'] as $placeholder => $label )
			{
				$output .= sprintf(
					'<li><label>%s</label>: %s</li>',
					$label,
					isset( $value[$i] ) ? $value[$i] : ''
				);
				$i ++;
			}
		}
		$output .= '</ul>';

		return $output;
	}
}
