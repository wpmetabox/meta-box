<?php
/**
 * HTML5 range field class.
 */
class RWMB_Range_Field extends RWMB_Number_Field
{
	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$output  = parent::html( $meta, $field );
		$output .= sprintf( '<span class="rwmb-output">%s</span>', $meta );
		return $output;
	}

	/**
	 * Enqueue styles
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-range', RWMB_CSS_URL . 'range.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-range', RWMB_JS_URL . 'range.js', array(), RWMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = wp_parse_args( $field, array(
			'min'  => 0,
			'max'  => 10,
			'step' => 1,
		) );

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['type'] = 'range';

		return $attributes;
	}

	/**
	 * Ensure number in range.
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return int
	 */
	static function value( $new, $old, $post_id, $field )
	{
		$new = intval( $new );
		$min = intval( $field['min'] );
		$max = intval( $field['max'] );

		if ( $new < $min )
		{
			return $min;
		}
		elseif ( $new > $max )
		{
			return $max;
		}

		return $new;
	}
}
