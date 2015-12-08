<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Select_Field' ) )
{
	class RWMB_Select_Field extends RWMB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'rwmb-select', RWMB_CSS_URL . 'select.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-select', RWMB_JS_URL . 'select.js', array(), RWMB_VER, true );
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
			$html = sprintf(
				'<select %s>',
				self::render_attributes( $field['attributes'] )
			);

			$html .= self::options_html( $field, $meta );

			$html .= '</select>';

			$html .= self::get_select_all_html( $field['multiple'] );

			return $html;
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
		 *
		 * @return void
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
			if ( ! $field['clone'] && $field['multiple'] )
				$field['field_name'] .= '[]';
			
			$field = parent::normalize_field( $field );			
			$field = wp_parse_args( $field, array(
				'size' => $field['multiple'] ? 5 : 0,
			) );
			
			$field['attributes'] = wp_parse_args( $field['attributes'], array(
				'multiple'     => $field['multiple'],
				'size'         => $field['size'],
			) );	

			return $field;
		}

		/**
		 * Creates html for options
		 *
		 * @param array $field
		 * @param mixed $meta
		 *
		 * @return array
		 */
		static function options_html( $field, $meta )
		{
			$html = '';
			if ( $field['placeholder'] )
			{
				$show_placeholder = ( 'select' === $field['type'] ) // Normal select field
					|| ( isset( $field['field_type'] ) && 'select' === $field['field_type'] ) // For 'post' field
					|| ( isset( $field['display_type'] ) && 'select' === $field['display_type'] ); // For 'taxonomy' field
				$html             = $show_placeholder ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
			}

			$option = '<option value="%s"%s>%s</option>';

			foreach ( $field['options'] as $value => $label )
			{
				$html .= sprintf(
					$option,
					$value,
					selected( in_array( $value, (array) $meta ), true, false ),
					$label
				);
			}

			return $html;
		}

		/**
		 * Output the field value
		 * Display unordered list of option labels, not option values
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return string Link(s) to post
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = self::get_value( $field, $args, $post_id );
			if ( ! $value )
				return '';

			$function = array( RW_Meta_Box::get_class_name( $field ), 'get_option_label' );

			if ( $field['clone'] )
			{
				$output = '<ul>';
				if ( $field['multiple'] )
				{
					foreach ( $value as $subvalue )
					{
						$output .= '<li>';
						array_walk_recursive( $subvalue, $function, $field );
						$output .= '<ul><li>' . implode( '</li><li>', $subvalue ) . '</li></ul>';
						$output .= '</li>';
					}
				}
				else
				{
					array_walk_recursive( $value, $function, $field );
					$output = '<li>' . implode( '</li><li>', $value ) . '</li>';
				}
				$output .= '</ul>';
			}
			else
			{
				if ( $field['multiple'] )
				{
					array_walk_recursive( $value, $function, $field );
					$output = '<ul><li>' . implode( '</li><li>', $value ) . '</li></ul>';
				}
				else
				{
					call_user_func_array( $function, array( &$value, 0, $field ) );
					$output = $value;
				}
			}

			return $output;
		}

		/**
		 * Get option label to display in the frontend
		 *
		 * @param int   $value Option value
		 * @param int   $index Array index
		 * @param array $field Field parameter
		 *
		 * @return string
		 */
		static function get_option_label( &$value, $index, $field )
		{
			$value = $field['options'][$value];
		}

		/**
		 * Get html for select all|none for multiple select
		 *
		 * @param $multiple
		 *
		 * @return string
		 */
		static function get_select_all_html( $multiple )
		{
			if ( $multiple === true )
			{
				return '<div class="rwmb-select-all-none">
						' . __( 'Select', 'meta-box' ) . ': <a data-type="all" href="#">' . __( 'All', 'meta-box' ) . '</a> | <a data-type="none" href="#">' . __( 'None', 'meta-box' ) . '</a>
					</div>';
			}
			return '';
		}
	}
}
