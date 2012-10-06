<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Select_Field' ) )
{
	class RWMB_Select_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return	void
		 */
		static function admin_enqueue_scripts( )
		{
			wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL.'select.css', RWMB_VER );
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
			if ( ! is_array( $meta ) )
				$meta = (array) $meta;

			$html = sprintf(
				'<select class="rwmb-select" name="%s" id="%s"%s>',
				$field['field_name'],
				$field['id'],
				$field['multiple'] ? ' multiple="multiple"' : ''
			);
			$option = '<option value="%s" %s>%s</option>';

			foreach ( $field['options'] as $value => $label )
			{
				$html .= sprintf(
					$option,
					$value,
					selected( in_array( $value, $meta ), true, false ),
					$label
				);
			}
			$html .= '</select>';

			return $html;
		}
	}
}