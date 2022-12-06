<?php
/**
 * The Button group.
 */
class RWMB_Button_Group_Field extends RWMB_Choice_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-button-group', RWMB_CSS_URL . 'button-group.css', [], RWMB_VER );
		wp_enqueue_script( 'rwmb-button-group', RWMB_JS_URL . 'button-group.js', [ 'rwmb' ], RWMB_VER, true );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$options = self::transform_options( $field['options'] );
		$walker  = new RWMB_Walker_Input_List( $field, $meta );

		$output  = sprintf(
			'<fieldset class="rwmb-button-input-list %s">',
			$field['inline'] ? 'rwmb-inline' : ''
		);
		$output .= $walker->walk( $options, -1 );
		$output .= '</fieldset>';

		return $output;
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
		$field = wp_parse_args( $field, [
			'inline' => true,
		] );

		$field = $field['multiple'] ? RWMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = RWMB_Input_Field::normalize( $field );

		$field['flatten'] = true;

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
		$attributes          = RWMB_Input_Field::get_attributes( $field, $value );
		$attributes['id']    = false;
		$attributes['type']  = $field['multiple'] ? 'checkbox' : 'radio';
		$attributes['value'] = $value;

		return $attributes;
	}
}
