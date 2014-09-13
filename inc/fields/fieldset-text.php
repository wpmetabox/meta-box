<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Fieldset_Text_Field' ) )
{
	class RWMB_Fieldset_Text_Field extends RWMB_Field
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
			if ( count( $meta ) == 1 && trim( $meta[0] ) == '' )
				$meta = '';

			$html   = array();
			$before = '<fieldset><legend>' . $field['desc'] . '</legend>';
			$after  = '</fieldset>';

			$tpl = '<label>%s <input type="text" class="rwmb-fieldset-text" name="%s[%s][%d]" placeholder="%s" value="%s" /></label>';

			for ( $n = 0; $n < $field['rows']; $n ++ )
			{
				foreach ( $field['options'] as $k => $v )
				{
					$fid = $field['id'];
					if ( is_array( $meta ) && ! empty( $meta ) )
						$html[] = sprintf( $tpl, $k, $fid, $v, $n, $k, $meta[$v][$n] );
					else
						$html[] = sprintf( $tpl, $k, $fid, $v, $n, $k, '' );
				}
				$html[] = '<br>';
			}

			$out = $before . implode( ' ', $html ) . $after;

			return $out;
		}

		/**
		 * Get meta value
		 *
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'] );

			if ( is_array( $meta ) && ! empty( $meta ) )
				$meta = $meta[0];

			return $meta;
		}

		/**
		 * Save meta value
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			update_post_meta( $post_id, $field['id'], $new, $old );
		}
	}
}
