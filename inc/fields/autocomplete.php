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
			wp_localize_script( 'rwmb-autocomplete', 'RWMB_Autocomplete', array( 'delete' => __( 'Delete', 'meta-box' ) ) );
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

			$options = array();
			foreach ( $field['options'] as $value => $label )
			{
				$options[] = array(
					'value' => $value,
					'label' => $label,
				);
			}

			// Input field that triggers autocomplete.
			// This field doesn't store field values, so it doesn't have "name" attribute.
			// The value(s) of the field is store in hidden input(s). See below.
			$html = sprintf(
				'<input type="text" class="rwmb-autocomplete" id="%s" data-name="%s" data-options="%s" size="%s">',
				$field['id'],
				$field['field_name'],
				esc_attr( json_encode( $options ) ),
				$field['size']
			);

			$html .= '<div class="rwmb-autocomplete-results">';

			// Each value is displayed with label and 'Delete' option
			// The hidden input has to have ".rwmb-*" class to make clone work
			$tpl = '
				<div class="rwmb-autocomplete-result">
					<div class="label">%s</div>
					<div class="actions">%s</div>
					<input type="hidden" class="rwmb-autocomplete-value" name="%s" value="%s">
				</div>
			';
			foreach ( $field['options'] as $value => $label )
			{
				if ( in_array( $value, $meta ) )
				{
					$html .= sprintf(
						$tpl,
						$label,
						__( 'Delete', 'meta-box' ),
						$field['field_name'],
						$value
					);
				}
			}
			$html .= '</div>'; // .rwmb-autocomplete-results

			return $html;
		}

		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
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

			return $meta;
		}

		/**
		 * Save meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
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
			$field = wp_parse_args( $field, array(
				'size' => 30,
			) );

			$field['multiple']   = true;
			$field['field_name'] = $field['id'];
			if ( ! $field['clone'] )
				$field['field_name'] .= '[]';

			return $field;
		}
	}
}
