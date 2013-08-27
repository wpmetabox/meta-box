<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Key_Val_Field' ) )
{
	class RWMB_Key_Val_Field
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
			$tpl = '<input type="text" id="%s" class="rwmb-key-val" name="%s[key]" value="%s"> <input type="text" class="rwmb-key-val" name="%s[val]" value="%s">';
			$html = '';

			foreach ( $meta as $i => $meta_field ) {
				$html .= sprintf( $tpl,
					$field['id'],
					$field['id'] . '[' . $i . ']',
					$meta_field['key'],
					$field['id'] . '[' . $i . ']',
					$meta_field['val']
				);
				$html .= '<br>';
			}

			if ( empty( $meta ) )
				$html .= sprintf( $tpl, $field['id'], $field['id'] . '[0]', '', $field['id'] . '[0]', '' );

			return $html;
		}

		static function meta( $meta, $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'], true );

			return $meta;
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
			$field['clone']	= true;

			return $field;
		}
	}
}