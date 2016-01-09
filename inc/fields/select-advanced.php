<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Select_Advanced_Field extends RWMB_Select_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'select2', RWMB_CSS_URL . 'select2/select2.css', array(), '3.2' );
		wp_enqueue_style( 'rwmb-select-advanced', RWMB_CSS_URL . 'select-advanced.css', array(), RWMB_VER );

		wp_register_script( 'select2', RWMB_JS_URL . 'select2/select2.min.js', array(), '3.2', true );
		wp_enqueue_script( 'rwmb-select', RWMB_JS_URL . 'select.js', array(), RWMB_VER, true );
		wp_enqueue_script( 'rwmb-select-advanced', RWMB_JS_URL . 'select-advanced.js', array( 'select2', 'rwmb-select' ), RWMB_VER, true );
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function html( $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$attributes = call_user_func( array( $field_class, 'get_attributes' ), $field, $meta );
		$html = sprintf(
			'<select %s>',
			self::render_attributes( $attributes )
		);

		$html .= self::options_html( $field, $meta );

		$html .= '</select>';

		$html .= self::get_select_all_html( $field['multiple'] );

		return $html;
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = wp_parse_args( $field, array(
			'js_options' => array(),
			'placeholder' => 'Select an item'
		) ); 
		
		$field = parent::normalize( $field );		

		$field['js_options'] = wp_parse_args( $field['js_options'], array(
			'allowClear'  => true,
			'width'       => 'off',
			'placeholder' => $field['placeholder'],
		) );

		return $field;
	}
	
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, array(
			'data-options'  => wp_json_encode( $field['js_options'] ),
		) );

		return $attributes;
	}
}
