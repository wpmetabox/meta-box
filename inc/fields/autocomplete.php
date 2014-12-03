<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Autocomplete_Field' ) )
{
	class RWMB_Autocomplete_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-autocomplete', RWMB_CSS_URL . 'autocomplete.css', array( 'wp-admin' ), RWMB_VER );
			wp_enqueue_script( 'rwmb-autocomplete', RWMB_JS_URL . 'autocomplete.js', array( 'jquery-ui-autocomplete' ), RWMB_VER, true );
			wp_localize_script( 'rwmb-autocomplete', 'translated_strings', array( 'delete' => __( 'Delete', 'meta-box' ) ) );
		}

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
			if ( ! is_array( $meta ) )
				$meta = array( $meta );

			$html = '<div class="lineAutocomplete">';

			$html .= '<div class="autocompleteInput">';
			$html .= '<input id="' . $field['id'] . 'Input" size="50">';
			$html .= '</div>';

			$html .= '<div class="autocompleteResults">';
			$tpl = '<div class="lineAutocomplete">
						<div class="id">
		 					<p>#%s</p>
		                </div>
		                <div class="name">
		 					<p>%s</p>
		                </div>
		                <div class="actions">
		                    <p>%s</p>
		                </div>
		                <div class="clear"></div>
		                <input type="hidden" class="rwmb-autocomplete" name="%s" value="%s">
		            </div>';

			foreach ( $field['options'] as $value => $label )
			{
				if ( in_array( $value, $meta ) )
				{
					$html .= sprintf(
						$tpl,
						$value,
						$label,
						__( 'Delete', 'meta-box' ),
						$field['field_name'],
						$value
					);
				}
			}
			$html .= '</div>';

			$html .= '</div>';

			$html .= '<script type="text/javascript">';
			$html .= 'var ' . $field['id'] . '_data = [';
			foreach ( $field['options'] as $value => $label )
			{
				if ( ! in_array( $value, $meta ) )
					$html .= sprintf(
						'"#%s - %s",',
						$value,
						$label
					);
			}
			$html .= '];';

			$html .= 'jQuery(document).ready(function() {';
			$html .= 'autoCompleteInit("' . $field['id'] . 'Input", "' . $field['field_name'] . '", ' . $field['id'] . '_data);';
			$html .= '});';

			$html .= '</script>';

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
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$meta = get_post_meta( $post_id, $field['id'], $field['clone'] );
			$meta = ( ! $saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;
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
			if ( ! $field['clone'] )
			{
				parent::save( $new, $old, $post_id, $field );

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
			$field['multiple']   = true;
			$field['field_name'] = $field['id'];
			if ( ! $field['clone'] )
				$field['field_name'] .= '[]';

			return $field;
		}
	}
}
