<?php

/*TODO:
* - render functions for checklist, select and select tree
* - hierarchy option  
* - standardize options for normalize
* - use render_attributes 
*/ 
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

abstract class RWMB_Object_Choice_Field extends RWMB_Field
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
		$field_class = RW_Meta_Box::get_class_name( $field );
		$meta = (array) $meta;
		$options = call_user_func( array( $field_class, 'get_options' ), $field );
		$output = '';
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				$output .= call_user_func( array( $field_class, 'render_list' ), $options, $meta, $field );
				break;
			case 'select_advanced':
			case 'select':
			case 'select_tree':
			default:
				$output .= call_user_func( array( $field_class, 'render_select' ), $options, $meta, $field );
				break;
		}
		return $output;
	}
	
	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'flat' 			=> true,
			'parent' 		=> 0,
			'query_args' 	=> array(),
			'field_type'	=> 'select'
		) );
		
		if( 'checkbox_tree' === $field['field_type'] )
		{
			$field['field_type'] = 'checkbox_list';
			$field['flat'] = false;
		}
		
		if( 'radio_list' === $field['field_type'] )
		{
			$field['flat'] = true;
		}
		
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				$field['multiple'] = 'radio_list' === $field['field_type'] ? false : true;
				$field = RWMB_Input_Field::normalize( $field );
				$field['attributes']['class'] = "rwmb-choice";
				$field['attributes']['id']   = false;
				$field['attributes']['type'] = 'radio_list' === $field['field_type'] ? 'radio' : 'checkbox';
				$field['attributes']['name'] .= 	! $field['clone'] && $field['multiple'] ? '[]' : '';			
				break;
			case 'select_advanced':
				$field['attributes']['class'] = "rwmb-choice rwmb-select_advanced";
				$field = RWMB_Select_Advanced_Field::normalize( $field );
				$field['flat'] = true;
				break;
			case 'select':
			case 'select_tree':
			default:
				$field = RWMB_Select_Field::normalize( $field );
				break;
		}

		return $field;
	}
	
	static function get_db_fields()
	{
		return array(
            'parent'    => '',
            'id'        => '',
            'label'     => '',              
        );
	}
	
	
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-object-choice', RWMB_CSS_URL . 'object-choice.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-object-choice', RWMB_JS_URL . 'object-choice.js', array(), RWMB_VER, true );
		RWMB_Select_Field::admin_enqueue_scripts();
		RWMB_Select_Advanced_Field::admin_enqueue_scripts();
	}
	
	static function render_list( $options, $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$db_fields = call_user_func( array( $field_class, 'get_db_fields' ), $field );
		$walker = new RWMB_Choice_List_Walker( $db_fields, $field, $meta );
		
		$output = '<ul class="rwmb-choice-list">';
		
		$output .= $walker->walk( $options, $field['flat'] ? -1 : 0 );
		$output .= '</ul>';
		return $output;
	}
	
	static function render_select( $options, $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$db_fields = call_user_func( array( $field_class, 'get_db_fields' ), $field );
		$walker = new RWMB_Select_Walker( $db_fields, $field, $meta );
		
		$output = sprintf(
			'<select %s>',
			self::render_attributes( $field['attributes'] )
		);
		if( 'select' === $field['field_type'] && false === $field['multiple'] )
		{
			$output .= isset( $field['placeholder'] ) ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
		}
		$output .= $walker->walk( $options, $field['flat'] ? -1 : 0 );
		$output .= '</select>';
		return $output;
	}
	
	static function render_select_tree( $options, $meta, $field )
	{
		
	}
		
	static function get_options( $field )
	{
		return array();
	}
}

abstract class RWMB_Walker extends Walker
{
    /**
    * Field data.
    *
    * @access public
    * @var string
    */
    public $field;
    public $meta = array();
    
    function __construct( $db_fields, $field, $meta )
    {
        $this->db_fields = wp_parse_args( (array) $db_fields, array(
            'parent'    => '',
            'id'        => '',
            'label'     => '',              
        ) );
       $this->field = $field; 
       $this->meta = (array) $meta;
    }
}

class RWMB_Select_Walker extends RWMB_Walker
{
	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output               Passed by reference. Used to append additional content.
	 * @param object $page                 Page data object.
	 * @param int    $depth                Depth of page. 
	 * @param int    $current_object_id    Page ID.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $label = $this->db_fields['label'];  
        $id =  $this->db_fields['id'];   
        $meta = $this->meta;
		$indent = str_repeat( "&nbsp;", $depth * 4 );
   
		$output .= sprintf(
			'<option value="%s" %s>%s%s</option>',
			$object->$id,
			selected( in_array( $object->$id, $meta ), 1, false ),
			$indent,
			$object->$label
		);
	}
}

class RWMB_Choice_List_Walker extends RWMB_Walker
{
  /**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of page. 
	 * @param array  $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "<ul class='rwmb-choice-list hidden'>";
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of page. 
	 * @param array  $args
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "</ul>";
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output               Passed by reference. Used to append additional content.
	 * @param object $page                 Page data object.
	 * @param int    $depth                Depth of page. 
	 * @param int    $current_object_id    Page ID.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $label = $this->db_fields['label'];  
        $id =  $this->db_fields['id'];   
        $meta = $this->meta;
		$attributes = $this->field['attributes'];
		$attributes['value'] = $object->$id;
   
		$output .= sprintf(
			'<li><label><input %s %s>%s</label>',
			RWMB_Field::render_attributes( $attributes ),			
			checked( in_array( $object->$id, $meta ), 1, false ),
			$object->$label
		);
	}

	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object. Not used.
	 * @param int    $depth Depth of page. Not Used.
	 * @param array  $args
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) {
		$output .= "</li>";
	}  
}