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
		$button = $field['clone'] ? self::add_clone_button( $field ) : '';

		// Closes the container
		$html = "{$button}</div>";

		return $html;
	}

	/**
	 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string
	 * This prevents using default value once the checkbox has been unchecked
	 *
	 * @link https://github.com/rilwis/meta-box/issues/6
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return int
	 */
	public static function value( $new, $old, $post_id, $field )
	{
		return empty( $new ) ? 0 : 1;
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
