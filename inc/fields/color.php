<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once RWMB_FIELDS_DIR . 'text.php';

if ( ! class_exists( 'RWMB_Color_Field' ) )
{
	class RWMB_Color_Field extends RWMB_Text_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-color', RWMB_CSS_URL . 'color.css', array( 'wp-color-picker' ), RWMB_VER );
			wp_enqueue_script( 'rwmb-color', RWMB_JS_URL . 'color.js', array( 'wp-color-picker' ), RWMB_VER, true );
		}

		/**
		 * Don't save '#' when no color is chosen
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
			return '#' === $new ? '' : $new;
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'       => 7,
				'maxlength'  => 7,
				'pattern'    => '^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$',
				'js_options' => array(),
			) );

			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'defaultColor' => false,
				'hide'         => true,
				'palettes'     => true,
			) );

			$field = parent::normalize_field( $field );

			$field['attributes'] = wp_parse_args( $field['attributes'], array(
				'data-options' => wp_json_encode( $field['js_options'] ),
			) );

			return $field;
		}
	}
}
