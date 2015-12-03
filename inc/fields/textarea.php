<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Textarea_Field' ) )
{
	class RWMB_Textarea_Field extends RWMB_Field
	{
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
			$attributes = $field['attributes'];
			return sprintf(
				'<textarea %s>%s</textarea>',
				self::render_attributes( $attributes ),
				$meta
			);
		}

		/**
		 * Escape meta for field output
		 *
		 * @param mixed $meta
		 *
		 * @return mixed
		 */
		static function esc_meta( $meta )
		{
			return is_array( $meta ) ? array_map( 'esc_textarea', $meta ) : esc_textarea( $meta );
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
			$field = parent::normalize_field( $field );
			$field = wp_parse_args( $field, array(
				'cols' 			=> 60,
				'rows' 			=> 3,	
				'maxlength' 	=> false,
				'wrap'			=> false,
				'readonly'		=> false,
				'placeholder'	=> '',						
			) );
			
			$field['attributes'] = wp_parse_args( $field['attributes'], array(
				'cols' 			=> $field['cols'],
				'rows' 			=> $field['rows'],
				'maxlength' 	=> $field['maxlength'],
				'wrap'			=> $field['wrap'],
				'readonly'		=> $field['readonly'],
				'placeholder'	=> $field['placeholder'],
			) );
			$field['attributes']['class'] .= ' large-text';

			return $field;
		}
	}
}
