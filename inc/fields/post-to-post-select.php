<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once RWMB_FIELDS_DIR . 'select-advanced.php';

if ( !class_exists( 'RWMB_Select_Advanced_Field' ) )
{
	class RWMB_P2P_Select extends RWMB_Select_Advanced_Field
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
			$connectables = get_posts(
				array(
					'post_type'=> $field['connected_type'],
					'nopaging' => true,
					'suppress_filters' => false 
				)
			);
			$new_options = array();
			foreach ($connectables as $c) {
				$new_options[$c->ID] = $c->post_title;	
			}
			$field['options'] = $new_options;
			parent::html ( $html, $meta, $field );
		}
		
		
		/**
		 * Standard meta retrieval
		 *
		 * @param mixed 	$meta
		 * @param int		$post_id
		 * @param array  	$field
		 * @param bool  	$saved
		 *
		 * @return mixed
		 */
		static function meta( $meta, $post_id, $saved, $field )
		{
			
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
				'js_options' => array(),
			) );

			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'allowClear'  => true,
				'width'       => 'resolve',
				'placeholder' => __( 'Select a value', 'rwmb' )
			) );

			return $field;
		}
	}
}