<?php
/**
 * The Switch field.
 *
 * @package Meta Box
 */

/**
 * Switch field class.
 */
class RWMB_Switch_Field extends RWMB_Input_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-switch', RWMB_CSS_URL . 'switch.css', '', RWMB_VER );
	}

	/**	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, 1 );
		$on  		= $field['on_label'];
		$off  		= $field['off_label'];
		$class 		= $field['style'];

		$output = sprintf(
			'<label class="rwmb-switch_label rwmb-switch--'. esc_attr( $class ) . '">
				<input %s %s>
				<span class="rwmb-switch_slider"></span>
				<span class="rwmb-switch_on">' . $on . '</span>
				<span class="rwmb-switch_off">' . $off . '</span>
				</label>
			',
			self::render_attributes( $attributes ),
			checked( ! empty( $meta ), 1, false )
		);

		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	
	public static function normalize( $field, $value = null ) {

		$field = wp_parse_args( $field, array(
			'style' 	=> 'rounded',
			'on_label' 	=> '',
			'off_label' => '',
		) );

		$field = parent::normalize( $field );
		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The attribute value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes           	= parent::get_attributes( $field, $value );
		$attributes['type'] 	= 'checkbox';

		return $attributes;
	}
}
