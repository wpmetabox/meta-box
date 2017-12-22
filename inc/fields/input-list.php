<?php
/**
 * The input list field which displays choices in a list of inputs.
 *
 * @package Meta Box
 */

/**
 * Input list field class.
 */
class RWMB_Input_List_Field extends RWMB_Choice_Field {
	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-input-list', RWMB_CSS_URL . 'input-list.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-input-list', RWMB_JS_URL . 'input-list.js', array(), RWMB_VER, true );
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
		$walker = new RWMB_Walker_Input_List( $db_fields, $field, $meta );
		$output = self::get_select_all_html( $field );
		$output .= sprintf( '<ul class="rwmb-input-list %s %s">',
			$field['collapse'] ? 'collapse' : '',
			$field['inline'] ? 'inline' : ''
		);
		$output .= $walker->walk( $options, $field['flatten'] ? - 1 : 0 );
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = $field['multiple'] ? RWMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = RWMB_Input_Field::normalize( $field );
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'collapse'        => true,
			'inline'          => null,
			'select_all_none' => false,
		) );

		$field['flatten'] = $field['multiple'] ? $field['flatten'] : true;
		$field['inline'] = ! $field['multiple'] && ! isset( $field['inline'] ) ? true : $field['inline'];

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
		$attributes           = RWMB_Input_Field::get_attributes( $field, $value );
		$attributes['id']     = false;
		$attributes['type']   = $field['multiple'] ? 'checkbox' : 'radio';
		$attributes['value']  = $value;

		return $attributes;
	}

	/**
	 * Get html for select all|none for multiple checkbox.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function get_select_all_html( $field ) {
		if ( $field['multiple'] && $field['select_all_none'] ) {
			return sprintf( '<p><button class="rwmb-input-list-select-all-none button" data-name="%s">%s</button></p>', $field['id'], __( 'Select All / None', 'meta-box' ) );
		}
		return '';
	}
}
