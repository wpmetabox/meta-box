<?php

if ( ! class_exists( 'RWMB_Checkbox_Field' ) ) 
{
	class RWMB_Checkbox_Field 
	{
		/**
		 * Additional actions for checkbox field
		 *
		 * @return void
		 */
		static function add_actions()
		{
			// Filter: Change wrapper HTML
			// Use priority = 1 to allow other scripts change this value
			add_filter( "rwmb_checkbox_end_html", array( __CLASS__, 'end_html' ), 1, 3 );
		}

		/**
		 * Get field end HTML
		 *
		 * @param string $end_html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function end_html( $end_html, $meta, $field )
		{
			return " <span class='description'>{$field['desc']}</span></div>";
		}

		/**
		 * Get field HTML
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{
			$checked = checked( ! empty( $meta ), true, false );
			$name    = " name='{$field['id']}'";
			$id      = " id='{$field['id']}'";
			$html    = "<input type='checkbox' class='rwmb-checkbox'{$name}{$id}{$checked} />";

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
		static function value( $new, $old, $post_id, $field ) 
		{
			return empty( $new ) ? 0 : 1;
		}
	}
}