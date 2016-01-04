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
		return '';
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
	}
	
	static function render_list( $options, $meta, $field )
	{
		
	}
	
	static function render_select( $options, $meta, $field )
	{
		
	}
	
	static function render_select_tree( $options, $meta, $field )
	{
		
	}
		
	static function query( $field )
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

class RWMB_Checklist_Walker extends RWMB_Walker
{
  /**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of page. 
	 * @param array  $args
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= "<ul class='rwmb-checklist-children hidden'>";
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
   
		/** This filter is documented in wp-includes/post-template.php */
		$output .= sprintf(
			'<li><label><input type="checkbox" class="rwmb-checkbox-list" name="%s" value="%s"%s> %s </label>',
            $this->field['field_name'],
			$object->$id,
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