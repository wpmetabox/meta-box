<?php
/**
 * The text fieldset field, which allows users to enter content for a list of text fields.
 *
 * @package Meta Box
 */

/**
 * Fieldset text class.
 */
class RWMB_Fieldset_Text_Field extends RWMB_Text_Field {
	/**
	 * Enqueue field scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-fieldset-text', RWMB_CSS_URL . 'fieldset-text.css', '', RWMB_VER );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$html = array();
		$tpl  = '<p><label>%s</label> %s</p>';

		foreach ( $field['options'] as $key => $label ) {
			$value                       = isset( $meta[ $key ] ) ? $meta[ $key ] : '';
			$field['attributes']['name'] = $field['field_name'] . "[{$key}]";
			$html[]                      = sprintf( $tpl, $label, parent::html( $value, $field ) );
		}

		$out = '<fieldset><legend>' . $field['desc'] . '</legend>' . implode( ' ', $html ) . '</fieldset>';

		return $out;
	}

	/**
	 * Do not show field description.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function input_description( $field ) {
		return '';
	}

	/**
	 * Do not show field description.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function label_description( $field ) {
		return '';
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field                       = parent::normalize( $field );
		$field['multiple']           = false;
		$field['attributes']['id']   = false;
		$field['attributes']['type'] = 'text';
		return $field;
	}

	/**
	 * Format value for the helper functions.
	 *
	 * @param array        $field   Field parameters.
	 * @param string|array $value   The field meta value.
	 * @param array        $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null     $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_value( $field, $value, $args, $post_id ) {
		$output = '<table><thead><tr>';
		foreach ( $field['options'] as $label ) {
			$output .= "<th>$label</th>";
		}
		$output .= '</tr></thead></tbody>';

		if ( ! $field['clone'] ) {
			$output .= self::format_single_value( $field, $value, $args, $post_id );
		} else {
			foreach ( $value as $subvalue ) {
				$output .= self::format_single_value( $field, $subvalue, $args, $post_id );
			}
		}
		$output .= '</tbody></table>';
		return $output;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		$output = '<tr>';
		foreach ( $value as $subvalue ) {
			$output .= "<td>$subvalue</td>";
		}
		$output .= '</tr>';
		return $output;
	}
}
