<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Input_Field' ) )
{
	abstract class RWMB_Input_Field extends RWMB_Field
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
			$attributes          = $field['attributes'];
			$attributes['value'] = $meta;

			return sprintf(
				'<input %s>%s',
				self::render_attributes( $attributes ),
				self::datalist_html( $field )
			);
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
				'datalist' => false,
				'readonly' => false,
			) );
			if ( $field['datalist'] )
			{
				$field['datalist'] = wp_parse_args( $field['datalist'], array(
					'id'      => $field['id'] . '_list',
					'options' => array(),
				) );
			}

			$field['attributes'] = wp_parse_args( $field['attributes'], array(
				'list'     => $field['datalist'] ? $field['datalist']['id'] : false,
				'readonly' => $field['readonly'],
			) );

			return $field;
		}

		/**
		 * Create datalist, if any
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function datalist_html( $field )
		{
			if ( empty( $field['datalist'] ) )
				return '';

			$datalist = $field['datalist'];
			$html     = sprintf(
				'<datalist id="%s">',
				$datalist['id']
			);

			foreach ( $datalist['options'] as $option )
			{
				$html .= sprintf( '<option value="%s"></option>', $option );
			}

			$html .= '</datalist>';

			return $html;
		}
	}
}
