<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Key_Value_Field' ) )
{
	class RWMB_Key_Value_Field extends RWMB_Field
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
			$tpl = '<input type="text" class="rwmb-key-val" name="%s[]" value="%s" placeholder="' . esc_attr__( 'Key', 'meta-box' ) . '">';
			$tpl .= '<input type="text" class="rwmb-key-val" name="%s[]" value="%s" placeholder="' . esc_attr__( 'Value', 'meta-box' ) . '">';

			$key = isset( $meta[0] ) ? $meta[0] : '';
			$val = isset( $meta[1] ) ? $meta[1] : '';

			$html = sprintf( $tpl, $field['field_name'], $key, $field['field_name'], $val );

			return $html;
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
			foreach ( $meta as &$pairs )
			{
				$pairs = array_map( 'esc_attr', $pairs );
			}
			return $meta;
		}

		/**
		 * Sanitize email
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function value( $new, $old, $post_id, $field )
		{
			foreach ( $new as &$arr )
			{
				if ( empty( $arr[0] ) && empty( $arr[1] ) )
					$arr = false;
			}

			$new = array_filter( $new );

			return $new;
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
			$field['clone']    = true;
			$field['multiple'] = false;

			return $field;
		}
	}
}
