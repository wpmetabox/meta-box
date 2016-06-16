<?php

/**
 * Checkbox field class.
 */
class RWMB_Checkbox_Field extends RWMB_Input_Field
{
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-checkbox', RWMB_CSS_URL . 'checkbox.css', array(), RWMB_VER );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	public static function html( $meta, $field )
	{
		$attributes = self::get_attributes( $field, 1 );
		$output     = sprintf(
			'<input %s %s>',
			self::render_attributes( $attributes ),
			checked( ! empty( $meta ), 1, false )
		);
		if ( $field['desc'] )
		{
			$output = "<label id='{$field['id']}_description' class='description'>$output {$field['desc']}</label>";
		}
		return $output;
	}

	/**
	 * Show end HTML markup for fields
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	public static function end_html( $meta, $field )
	{
		return RWMB_Clone::add_clone_button( $field ) . '</div>';
	}

	/**
	 * Format a single value for the helper functions.
	 * @param array  $field Field parameter
	 * @param string $value The value
	 * @return string
	 */
	public static function format_single_value( $field, $value )
	{
		return $value ? __( 'Yes', 'meta-box' ) : __( 'No', 'meta-box' );
	}
}
