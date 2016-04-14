<?php
/**
 * Abstract field to select an object: post, user, taxonomy, etc.
 */
abstract class RWMB_Object_Choice_Field extends RWMB_Choice_Field
{
	/**
	 * Get field HTML
	 *
	 * @param mixed $options
	 * @param mixed $db_fields
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	public static function walk( $options, $db_fields, $meta, $field )
	{
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				return RWMB_Input_List_Field::walk( $options, $db_fields, $meta, $field );
				break;
			case 'select_tree':
				return RWMB_Select_Tree_Field::walk( $options, $db_fields, $meta, $field );
				break;
			case 'select_advanced':
				return RWMB_Select_Advanced_Field::walk( $options, $db_fields, $meta, $field );
				break;
			case 'select':
			default:
				return RWMB_Select_Field::walk( $options, $db_fields, $meta, $field );
				break;
		}
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'flatten'    => true,
			'query_args' => array(),
			'field_type' => 'select_advanced',
		) );

		if ( 'checkbox_tree' === $field['field_type'] )
		{
			$field['field_type'] = 'checkbox_list';
			$field['flatten']    = false;
		}

		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				$field['multiple'] = 'radio_list' === $field['field_type'] ? false : true;
				return RWMB_Input_List_Field::normalize( $field );
				break;
			case 'select_advanced':
				return RWMB_Select_Advanced_Field::normalize( $field );
				break;
			case 'select_tree':
				return RWMB_Select_Tree_Field::normalize( $field );
				break;
			case 'select':
			default:
				return RWMB_Select_Field::normalize( $field );
				break;
		}
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null )
	{
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				return RWMB_Input_List_Field::get_attributes( $field, $value );
				break;
			case 'select_advanced':
				$attributes = RWMB_Select_Advanced_Field::get_attributes( $field, $value );
				$attributes['class'] .= ' rwmb-select_advanced';
				return $attributes;
				break;
			case 'select_tree':
				return RWMB_Select_Tree_Field::get_attributes( $field, $value );
				break;
			case 'select':
			default:
				return RWMB_Select_Field::get_attributes( $field, $value );
				break;
		}
	}

	/**
	 * Get field names of object to be used by walker
	 * @return array
	 */
	public static function get_db_fields()
	{
		return array(
			'parent' => '',
			'id'     => '',
			'label'  => '',
		);
	}

	/**
	 * Save meta value
	 *
	 * @param $new
	 * @param $old
	 * @param $post_id
	 * @param $field
	 */
	static function save( $new, $old, $post_id, $field )
	{
		delete_post_meta( $post_id, $field['id'] );
		parent::save( $new, array(), $post_id, $field );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		RWMB_Input_List_Field::admin_enqueue_scripts();
		RWMB_Select_Field::admin_enqueue_scripts();
		RWMB_Select_Tree_Field::admin_enqueue_scripts();
		RWMB_Select_Advanced_Field::admin_enqueue_scripts();
	}

	/**
	 * Get options for walker
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	public static function get_options( $field )
	{
		return array();
	}
}
