<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once RWMB_FIELDS_DIR . 'text.php';

if ( ! class_exists( 'RWMB_URL_Field' ) )
{
	class RWMB_URL_Field extends RWMB_Text_Field
	{
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
			return sprintf(
				'<input type="url" class="rwmb-url" name="%s" id="%s" value="%s" size="%s" />',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['size']
			);
		}

		
		/**
		 * Sanitizes url
		 *
		 * @param $post_id
		 * @param $field
		 * @param $old
		 * @param $new
		 *
		 * @return $new
		 */
		static function value( $new, $old, $post_id, $field) 
		{
			$new = esc_url($new);
			return $new;
		}
	}
}