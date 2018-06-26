<?php
/**
 * The text list field which allows users to enter multiple texts.
 *
 * @package Meta Box
 */

/**
 * Text list field class.
 */
class RWMB_Text_List_Field extends RWMB_Multiple_Values_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-text-list', RWMB_CSS_URL . 'text-list.css', '', RWMB_VER );
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
		if ( empty( $field['options'] ) ) {
			return '';
		}
		$html  = array();
		$input = '<label><span class="rwmb-text-list-label">%s</span> <input type="text" class="rwmb-text-list" name="%s" value="%s" placeholder="%s"></label>';

		$count = 0;
		foreach ( $field['options'] as $placeholder => $label ) {
			$html[] = sprintf(
				$input,
				$label,
				$field['field_name'],
				isset( $meta[ $count ] ) ? esc_attr( $meta[ $count ] ) : '',
				$placeholder
			);
			$count ++;
		}

		return implode( ' ', $html );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		if ( ! $field['clone'] ) {
			$field['class'] .= ' rwmb-text_list-non-cloneable';
		}
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
		$output .= '</tr></thead><tbody>';

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

	/**
	 * Save meta value.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 */
	public static function save( $new, $old, $post_id, $field ) {
		if ( empty( $field['id'] ) || ! $field['save_field'] ) {
			return;
		}
		$storage = $field['storage'];
		$storage->delete( $post_id, $field['id'] );
		parent::save( $new, array(), $post_id, $field );
	}
}
