<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Key_Val_Field' ) )
{
	class RWMB_Key_Val_Field
	{
		static function html( $html, $meta, $field )
		{
			$tpl = '<input type="text" class="rwmb-key-val" name="%s[]" value="%s"> <input type="text" class="rwmb-key-val" name="%s[]" value="%s">';

			$key = isset( $meta[0] ) ? $meta[0] : '';
			$val = isset( $meta[1] ) ? $meta[1] : '';

			$html = sprintf( $tpl, $field['field_name'], $key, $field['field_name'], $val );

			return $html;
		}

		static function meta( $meta, $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'], true );

			if ( empty( $meta ) )
				$meta = '';

			return $meta;
		}

		static function save( $new, $old, $post_id, $field )
		{
			foreach ( $new as &$arr ) {
				if ( empty( $arr[0] ) && empty( $arr[1] ) )
					$arr = false;
			}

			$new = array_filter( $new );

			RW_Meta_Box::save( $new, $old, $post_id, $field );
		}

		static function normalize_field( $field )
		{
			$field['clone'] = true;
			$field['multiple'] = false;

			return $field;
		}
	}
}
