<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once RWMB_FIELDS_DIR . 'select-advanced.php';

if ( !class_exists( 'RWMB_Posts_Field' ) )
{
	class RWMB_Posts_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			RWMB_Select_Advanced_Field::admin_enqueue_scripts();
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
			$field['options'] = self::get_options( $field );
			switch( $field['field_type'] ) {
				case 'select':
					$html = RWMB_Select_Field::html( $html, $meta, $field );
					break;
				case 'select_advanced':
				default:
					$html = RWMB_Select_Advanced_Field::html( $html, $meta, $field );
			}

			return $html;
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
			$field = RWMB_Select_Advanced_Field::normalize_field( $field );	
			
			$field = wp_parse_args( $field, array(
				'post_type' => 'post',
				'field_type' => 'select_advanced'
			) );
			$field['query_args'] = wp_parse_args( $field['query_args'], array(
				'post_type' => $field['post_type'],
				'post_status' => 'publish',
				'posts_per_page'=>'-1'
			) );
			
			
			return $field;
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
			return  RWMB_Select_Advanced_Field::meta( $meta, $post_id, $saved, $field );
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
			return  RWMB_Select_Advanced_Field::save( $new, $old, $post_id, $field );

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
			$results = get_posts( $field['query_args']);
			$options = array();
			foreach( $results as $result ) {
				$options[$result->ID] = $result->post_title;	
			}
			return $options;
		}
	}
}