<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
require_once RWMB_FIELDS_DIR . 'select-advanced.php';
require_once RWMB_FIELDS_DIR . 'checkbox-list.php';

if ( ! class_exists( 'RWMB_Taxonomy_Field' ) )
{
	class RWMB_Taxonomy_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			RWMB_Select_Advanced_Field::admin_enqueue_scripts();
			wp_enqueue_style( 'rwmb-taxonomy', RWMB_CSS_URL . 'taxonomy.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-taxonomy', RWMB_JS_URL . 'taxonomy.js', array( 'jquery', 'rwmb-select-advanced', 'wp-ajax-response' ), RWMB_VER, true );
		}

		/**
		 * Add default value for 'taxonomy' field
		 *
		 * @param $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$default_args = array(
				'hide_empty' => false,
			);
			
			//Set default args
			$field['options']['args'] = ( ! isset( $field['options']['args'] ) ) ? $default_args : wp_parse_args( $field['options']['args'], $default_args );
			
			//Field name be an array by default
			$field['field_name'] = "{$field['id']}[]";
			
			switch( $field['options']['type'] )
			{
				case 'select_advanced':
					$field = RWMB_Select_Advanced_Field::normalize_field( $field );		
					break;
				case 'checkbox_list':
				case 'checkbox_tree':
					$field = RWMB_Checkbox_List_Field::normalize_field( $field );	
					break;
				case 'select':	
				case 'select_tree':
					$field = RWMB_Select_Field::normalize_field( $field );
					break;				
				default:
					$field['options']['type'] = 'select';
					$field = RWMB_Select_Field::normalize_field( $field );
			}

			if ( in_array( $field['options']['type'], array( 'checkbox_tree', 'select_tree' ) ) )
			{
				if ( isset( $field['options']['args']['parent'] ) )
				{
					$field['options']['parent'] = $field['options']['args']['parent'];
					unset( $field['options']['args']['parent'] );
				}
				else
				{
					$field['options']['parent'] = 0;
				}
			}

			return $field;
		}

		/**
		 * Get field HTML
		 *
		 * @param $html
		 * @param $field
		 * @param $meta
		 *
		 * @return string
		 */
		static function html( $html, $meta, $field )
		{

			$options = $field['options'];
			$terms   = get_terms( $options['taxonomy'], $options['args'] );
			
			$field['options'] = self::get_options( $terms );

			$html = '';
			
			switch( $options['type'] )
			{
				case 'checkbox_list':
					$html = RWMB_Checkbox_List_Field::html( $html, $meta, $field );
					break;
				case 'checkbox_tree':
					$elements = self::process_terms( $terms );
					$html    .= self::walk_checkbox_tree( $meta, $field, $elements, $options['parent'], true );
					break;
				case 'select_tree':
					$elements = self::process_terms( $terms );
					$html    .= self::walk_select_tree( $meta, $field, $elements, $options['parent'], '', true );
					break;
				case 'select_advanced':
					$html = RWMB_Select_Advanced_Field::html( $html, $meta, $field );
					break;
				case 'select':
				default:	
					$html = RWMB_Select_Field::html( $html, $meta, $field );
			}

			return $html;
		}

		/**
		 * Walker for displaying checkboxes in treeformat
		 *
		 * @param      $meta
		 * @param      $field
		 * @param      $elements
		 * @param int  $parent
		 * @param bool $active
		 *
		 * @return string
		 */
		static function walk_checkbox_tree( $meta, $field, $elements, $parent = 0, $active = false )
		{
			if ( ! isset( $elements[$parent] ) )
				return;
			$terms  = $elements[$parent];
			$field['options'] = self::get_options( $terms );	
			$hidden = ( !$active ? 'hidden' : '' );

			$html = "<ul class = 'rw-taxonomy-tree {$hidden}'>";
			$li = '<li><label><input type="checkbox" name="%s" value="%s" %s /> %s</label>';
			foreach ( $terms as $term )
			{
				$html .= sprintf(
					$li,
					$field['field_name'],
					$term->slug,
					checked( in_array( $term->slug, $meta ), true, false ),
					$term->name
				);
				$html .= self::walk_checkbox_tree( $meta, $field, $elements, $term->term_id, ( in_array( $term->slug, $meta ) ) && $active ) . '</li>';
			}
			$html .= '</ul>';

			return $html;
		}

		/**
		 * Walker for displaying select in treeformat
		 *
		 * @param        $meta
		 * @param        $field
		 * @param        $elements
		 * @param int    $parent
		 * @param string $parent_slug
		 * @param bool   $active
		 *
		 * @return string
		 */
		static function walk_select_tree( $meta, $field, $elements, $parent = 0, $parent_slug = '', $active = false )
		{
			if ( ! isset( $elements[$parent] ) )
				return;
			$terms    = $elements[$parent];
			$field['options'] = self::get_options( $terms );	
			$hidden   = $active ? 'active' : 'disabled';
			$disabled = disabled( $active, false, false );
			$id       = empty( $parent_slug ) ? '' : " id='rwmb-taxonomy-{$parent_slug}'";

			$html  = "<div{$id} class='rw-taxonomy-tree {$hidden}'>";
			$html .= RWMB_Select_Field::html( $html, $meta, $field );
			foreach ( $terms as $term )
			{
				$html .= self::walk_select_tree( $meta, $field, $elements, $term->term_id, $term->slug, in_array( $term->slug, $meta ) && $active ) . '</li>';
			}
			$html .= '</div>';

			return $html;
		}

		/**
		 * Processes terms into indexed array for walker functions
		 *
		 * @param $terms
		 *
		 * @internal param $field
		 * @return array
		 */
		static function process_terms( $terms )
		{
			$elements = array();
			foreach ( $terms as $term )
			{
				$elements[$term->parent][] = $term;
			}
			return $elements;
		}
		
		/**
		 * Get options for selects, checkbox list, etc via the terms
		 *
		 * @param $terms array of term objects
		 *
		 * @param $options array
		 */
		static function get_options( $terms = array() )
		{
			$options = array();
			foreach( $terms as $term )
			{
				$options[$term->slug] = $term->name;
			}
			return $options;
		}

		/**
		 * Save post taxonomy
		 *
		 * @param $post_id
		 * @param $field
		 * @param $old
		 *
		 * @param $new
		 */
		static function save( $new, $old, $post_id, $field )
		{
			wp_set_object_terms( $post_id, $new, $field['options']['taxonomy'] );
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
			$options = $field['options'];

			$meta = wp_get_post_terms( $post_id, $options['taxonomy'] );
			$meta = is_array( $meta ) ? $meta : (array) $meta;
			$meta = wp_list_pluck( $meta, 'slug' );

			return $meta;
		}
	}
}
