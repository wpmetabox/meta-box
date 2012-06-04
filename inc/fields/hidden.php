<?php
// Prevent loading this file directly - Busted!
if ( ! class_exists('WP') )
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'RWMB_Hidden_Field' ) )
{
	class RWMB_Hidden_Field
	{
		/**
		 * Show begin HTML markup for fields
		 *
		 * @param string $html
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $html, $meta, $field )
		{
			return '<div class="hidden">';
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
			$val   = " value='{$field['std']}'";
			$name  = " name='{$field['field_name']}'";
			$id    = " id='{$field['id']}'";
			$html .= "<input type='hidden' class='rwmb-hidden'{$name}{$id}{$val} />";

			return $html;
		}
	}
}