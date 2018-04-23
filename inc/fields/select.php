<?php
/**
 * The select field.
 *
 * @package Meta Box
 */

/**
 * Select field class.
 */
class RWMB_Select_Field extends RWMB_Choice_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-select', RWMB_JS_URL . 'select.js', array( 'jquery' ), RWMB_VER, true );
	}

	/**
	 * Walk options.
	 *
	 * @param array $field     Field parameters.
	 * @param mixed $options   Select options.
	 * @param mixed $db_fields Database fields to use in the output.
	 * @param mixed $meta      Meta value.
	 *
	 * @return string
	 */
	public static function walk( $field, $options, $db_fields, $meta ) {
		$attributes                  = self::call( 'get_attributes', $field, $meta );
		$attributes['data-selected'] = $meta;
		$walker                      = new RWMB_Walker_Select( $db_fields, $field, $meta );
		$output                      = sprintf(
			'<select %s>',
			self::render_attributes( $attributes )
		);
		if ( false === $field['multiple'] ) {
			$output .= $field['placeholder'] ? '<option value="">' . esc_html( $field['placeholder'] ) . '</option>' : '';
		}
		$output .= $walker->walk( $options, $field['flatten'] ? - 1 : 0 );
		$output .= '</select>';
		$output .= self::get_select_all_html( $field );
		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = $field['multiple'] ? RWMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = wp_parse_args( $field, array(
			'select_all_none' => false,
		) );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'multiple' => $field['multiple'],
		) );

		return $attributes;
	}

	/**
	 * Get html for select all|none for multiple select.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function get_select_all_html( $field ) {
		if ( $field['multiple'] && $field['select_all_none'] ) {
			return '<div class="rwmb-select-all-none">' . __( 'Select', 'meta-box' ) . ': <a data-type="all" href="#">' . __( 'All', 'meta-box' ) . '</a> | <a data-type="none" href="#">' . __( 'None', 'meta-box' ) . '</a></div>';
		}
		return '';
	}
}
