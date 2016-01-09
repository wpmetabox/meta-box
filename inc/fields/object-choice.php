<?php
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
			case 'select_tree':
				$output .= call_user_func( array( $field_class, 'render_select_tree' ), $options, $meta, $field );
				break;
			case 'select_advanced':
			case 'select':
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
			'flatten' 			=> true,
			'query_args' 	=> array(),
			'field_type'	=> 'select'
		) );
		
		if( 'checkbox_tree' === $field['field_type'] )
		{
			$field['field_type'] = 'checkbox_list';
			$field['flatten'] = false;
		}
		
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				$field['flatten'] = 'radio_list' === $field['field_type'] ? true : $field['flatten'];
				$field['multiple'] = 'radio_list' === $field['field_type'] ? false : true;
				$field = RWMB_Input_Field::normalize( $field );
				break;
			case 'select_advanced':
				$field = RWMB_Select_Advanced_Field::normalize( $field );
				$field['flatten'] = true;
				break;
			case 'select_tree':
				$field = RWMB_Select_Field::normalize( $field );
				$field['multiple'] = true;
				break;
			case 'select':
			default:
				$field = RWMB_Select_Field::normalize( $field );
				break;
		}
		
		return $field;
	}
	
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		switch ( $field['field_type'] )
		{
			case 'checkbox_list':
			case 'radio_list':
				$attributes = RWMB_Input_Field::get_attributes( $field, $value );
				$attributes['class'] = "rwmb-choice";
				$attributes['id']   = false;
				$attributes['type'] = 'radio_list' === $field['field_type'] ? 'radio' : 'checkbox';			
				break;
			case 'select_advanced':
				$attributes = RWMB_Select_Advanced_Field::get_attributes( $field, $value );
				$attributes['class'] = "rwmb-choice rwmb-select_advanced";
				break;
			case 'select_tree':
				$attributes = RWMB_Select_Field::get_attributes( $field, $value );
				$attributes['multiple'] = false;
				break;
			case 'select':
			default:
				$attributes = RWMB_Select_Field::get_attributes( $field, $value );
				break;
		}
		
		$attributes['name'] .= ! $field['clone'] && $field['multiple'] ? '[]' : ''; 
			
		return $attributes;
	}
	
	/**
	 * Get field names of object to be used by walker
	 *
	 * @return array
	 */
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
	
	/**
	 * Render checkbox_list or radio_list using walker
	 *
	 * @param $options
	 * @param $meta
	 * @param $field
	 *
	 * @return array
	 */
	static function render_list( $options, $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$db_fields = call_user_func( array( $field_class, 'get_db_fields' ), $field );
		$walker = new RWMB_Choice_List_Walker( $db_fields, $field, $meta );
		
		$output = '<ul class="rwmb-choice-list">';
		
		$output .= $walker->walk( $options, $field['flatten'] ? -1 : 0 );
		$output .= '</ul>';
		return $output;
	}
	
	/**
	 * Render select or select_advanced using walker
	 *
	 * @param $options
	 * @param $meta
	 * @param $field
	 *
	 * @return array
	 */
	static function render_select( $options, $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$attributes = call_user_func( array( $field_class, 'get_attributes' ), $field, $meta );
		$db_fields = call_user_func( array( $field_class, 'get_db_fields' ), $field );
		$walker = new RWMB_Select_Walker( $db_fields, $field, $meta );
		
		$output = sprintf(
			'<select %s>',
			self::render_attributes( $attributes )
		);
		if( 'select' === $field['field_type'] && false === $field['multiple'] )
		{
			$output .= isset( $field['placeholder'] ) ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
		}
		$output .= $walker->walk( $options, $field['flatten'] ? -1 : 0 );
		$output .= '</select>';
		return $output;
	}
	
	/**
	 * Render select_tree
	 *
	 * @param $options
	 * @param $meta
	 * @param $field
	 *
	 * @return array
	 */
	static function render_select_tree( $options, $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$db_fields = call_user_func( array( $field_class, 'get_db_fields' ), $field );
		$walker = new RWMB_Select_Tree_Walker( $db_fields, $field, $meta );
		$output = $walker->walk( $options );

		return $output;
	}
	
	/**
	 * Get options for walker
	 *
	 * @param array $field
	 *
	 * @return array
	 */	
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
	 * @param object $object               Item
	 * @param int    $depth                Depth of Item. 
	 * @param int    $current_object_id    Item id.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) 
	{
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
	 * @param int    $depth  Depth of item. 
	 * @param array  $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) 
	{
		$output .= "<ul class='rwmb-choice-list'>";
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of item. 
	 * @param array  $args
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) 
	{
		$output .= "</ul>";
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output               Passed by reference. Used to append additional content.
	 * @param object $object               Item data object.
	 * @param int    $depth                Depth of item. 
	 * @param int    $current_object_id    Item ID.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) 
	{
		$label					= $this->db_fields['label'];  
        $id 					= $this->db_fields['id'];   
        $meta 					= $this->meta;
		$field 					= $this->field;
		$field_class 			= RW_Meta_Box::get_class_name( $field );
		$attributes 			= call_user_func( array( $field_class, 'get_attributes' ), $field, $object->$id );
   
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
	public function end_el( &$output, $page, $depth = 0, $args = array() ) 
	{
		$output .= "</li>";
	}  
}

class RWMB_Select_Tree_Walker 
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
	
	function walk( $options )
	{
		$parent = $this->db_fields['parent'];
		$label = $this->db_fields['label'];  
		$id =  $this->db_fields['id'];  
		$children = array();
		
		foreach( $options as $o )
		{
			$children[$o->$parent][] = $o;
		}
		$top_level = isset( $children[0] ) ? 0 : $options[0]->$parent;
		return $this->display_level( $children, $top_level, true );
	}
	
	function display_level( $options, $parent_id = 0, $active = false )
	{
		$parent 				= $this->db_fields['parent'];
		$label					= $this->db_fields['label'];  
		$id 					=  $this->db_fields['id'];  
		$field 					= $this->field;
		$meta					= $this->meta;
		$walker 				= new RWMB_Select_Walker( $this->db_fields, $this->field, $this->meta );
		$field_class 			= RW_Meta_Box::get_class_name( $field );
		$attributes 			= call_user_func( array( $field_class, 'get_attributes' ), $field, $meta );
		
		$children = $options[$parent_id];
		$output = sprintf( 
			'<div class="rwmb-select-tree %s" data-parent-id="%s"><select %s>', 
			$active ? '' : 'hidden', 
			$parent_id, 
			RWMB_Field::render_attributes( $attributes ) 
		);
		$output .= isset( $field['placeholder'] ) ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
		$output .= $walker->walk( $children, -1 );
		$output .= '</select>';
		
		foreach( $children as $c)
		{
			if( isset( $options[$c->$id] ) )
			{
				$output .= $this->display_level( $options, $c->$id, in_array( $c->$id, $meta ) && $active );
			}
			
		}
		
		$output .= '</div>';
		
		return $output;		
	}
}