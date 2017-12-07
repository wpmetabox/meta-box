<?php
/**
 * The switch checkbox field.
 *
 * @package Meta Box
 */

/**
 * Button field class.
 */
class RWMB_Switch_Field extends RWMB_Input_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'rwmb-switch', RWMB_CSS_URL . 'switch.css', '', RWMB_VER );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$attributes = self::get_attributes( $field, 1 );
		$title_before	= '';
		$title_after	= '';
		$class			= '';

		// Title before after switch
		if ( $field['on_label'] ) {
			$title_before  = $field['on_label'];
		}
		if ( $field['off_label'] ) {
			$title_after  = $field['off_label'];
		}
		// style switch
		switch ($field['style']) {
			case 'square':
				$class = $field['style'];
				break;
			default:
				$class = "rounded";
				break;
		}

		$output = sprintf(
			"<div class='rwmb-switch-input'><label class='rwmb-switch ". $class ."'>
				<input %s %s>
				<span class='slider'></span>
				<span class='title-before'>" . $title_before . " </span>
				<span class='title-after'>" . $title_after . " </span>
				</label></div>
			",
			self::render_attributes( $attributes ),
			checked( ! empty( $meta ), 1, false )
		);

		return $output;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The attribute value.
	 * @return array
	 */
	public static function normalize( $field, $value = null ) {

		$field = wp_parse_args( $field, array(
			'style' 	=> 'rounded',
			'on_label' 	=> __( 'On', 'meta-box' ),
			'off_label' => __( 'Off', 'meta-box' ),
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
