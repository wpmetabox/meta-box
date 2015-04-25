<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select-advanced" field is loaded
require_once RWMB_FIELDS_DIR . 'select-advanced.php';

if ( ! class_exists( 'RWMB_Post_Field' ) )
{
	class RWMB_Post_Field extends RWMB_Select_Advanced_Field
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
			$field['options'] = self::get_options( $field );
			switch ( $field['field_type'] )
			{
				case 'select':
					return RWMB_Select_Field::html( $meta, $field );
				case 'select_advanced':
				default:
					return RWMB_Select_Advanced_Field::html( $meta, $field );
			}
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
				'post_type'  => 'post',
				'field_type' => 'select_advanced',
				'parent'     => false,
				'query_args' => array(),
			) );

			$post_type_object = get_post_type_object( $field['post_type'] );
			$post_type_label  = $post_type_object->labels->singular_name;

			$field['std'] = empty( $field['std'] ) ? sprintf( __( 'Select a %s', 'meta-box' ), $post_type_label ) : $field['std'];

			if ( $field['parent'] )
			{
				$field['multiple']   = false;
				$field['field_name'] = 'parent_id';
			}

			$field['query_args'] = wp_parse_args( $field['query_args'], array(
				'post_type'      => $field['post_type'],
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			) );

			switch ( $field['field_type'] )
			{
				case 'select':
					return RWMB_Select_Field::normalize_field( $field );
					break;
				case 'select_advanced':
				default:
					return RWMB_Select_Advanced_Field::normalize_field( $field );
			}
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
			if ( isset( $field['parent'] ) && $field['parent'] )
			{
				$post = get_post( $post_id );

				return $post->post_parent;
			}

			return parent::meta( $post_id, $saved, $field );
		}

		/**
		 * Get posts
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function get_options( $field )
		{
			$options = array();
			$query   = new WP_Query( $field['query_args'] );
			if ( $query->have_posts() )
			{
				while ( $query->have_posts() )
				{
					$post               = $query->next_post();
					$options[$post->ID] = $post->post_title;
				}
			}

			return $options;
		}

		/**
		 * Output the field value
		 * Display link to post
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

			if ( $field['clone'] )
			{
				$output = '<ul>';
				if ( $field['multiple'] )
				{
					$output .= '<li>';
					foreach ( $value as $subvalue )
					{
						$output .= '<ul><li>' . implode( '</li><li>', array_map( array( __CLASS__, 'get_post_link' ), $subvalue ) ) . '</li></ul>';
					}
					$output .= '</li>';
				}
				else
				{
					$output .= '<li>' . implode( '</li><li>', array_map( array( __CLASS__, 'get_post_link' ), $value ) ) . '</li>';
				}
				$output .= '</ul>';
			}
			else
			{
				$output = $field['multiple'] ? '<ul><li>' . implode( '</li><li>', array_map( array( __CLASS__, 'get_post_link' ), $value ) ) . '</li></ul>' : self::get_post_link( $value );
			}

			return $output;
		}

		/**
		 * Get post link to output in the frontend
		 *
		 * @param int $post_id Post ID
		 *
		 * @return string
		 */
		static function get_post_link( $post_id )
		{
			return sprintf(
				'<a href="%s" title="%s">%s</a>',
				esc_url( get_permalink( $post_id ) ),
				the_title_attribute( array(
					'post' => $post_id,
					'echo' => false,
				) ),
				get_the_title( $post_id )
			);
		}
	}
}
