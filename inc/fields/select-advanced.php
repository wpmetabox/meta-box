<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'RWMB_Select_Advanced_Field' ) )
{
	class RWMB_Select_Advanced_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'select2', RWMB_CSS_URL . 'select2-css/select2.css', array(), '3.2' );
			wp_enqueue_style( 'rwmb-select-advanced', RWMB_CSS_URL . 'select-advanced.css', array(), RWMB_VER );
			wp_register_script( 'select2',  RWMB_JS_URL . 'select2-js/select2.js', array(), '3.2', true );
			wp_enqueue_script( 'select_advanced',  RWMB_JS_URL . 'select-advanced.js', array('select2'), RWMB_VER, true );
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
			$html = sprintf(
				'<select class="rwmb-select-advanced" name="%s" id="%s"%s data-options ="%s">',
				$field['field_name'],
				$field['id'],
				$field['multiple'] ? ' multiple="multiple"' : '',
				esc_attr( json_encode( $field['js_options'] ))
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

		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $meta
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $meta, $post_id, $saved, $field )
		{
			$single = $field['clone'] || !$field['multiple'];
			$meta = get_post_meta( $post_id, $field['id'], $single );
			$meta = ( !$saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;

			$meta = array_map( 'esc_attr', (array) $meta );

			return $meta;
		}

		/**
		 * Save meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			if ( !$field['clone'] )
			{
				RW_Meta_Box::save( $new, $old, $post_id, $field );
				return;
			}

			if ( empty( $new ) )
				delete_post_meta( $post_id, $field['id'] );
			else
				update_post_meta( $post_id, $field['id'], $new );
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
			$field = wp_parse_args( $field, array(
				'js_options' => array(),
			) );

			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'allowClear' => true,
				'width' => 'resolve',
				'placeholder' => "Select a Value"
			) );
			
			$field['field_name'] = $field['id'];
			if ( !$field['clone'] && $field['multiple'] )
				$field['field_name'] .= '[]';
			return $field;
		}
	}
}