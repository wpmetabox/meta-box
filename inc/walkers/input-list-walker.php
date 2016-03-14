<?php
/**
 * Input List Walker
 * For checkbox and radio list fields
 */
class RWMB_Input_List_Walker extends RWMB_Walker
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
		$output .= "<ul class='rwmb-input-list'>";
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
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @param object $object            Item data object.
	 * @param int    $depth             Depth of item.
	 * @param int    $current_object_id Item ID.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 )
	{
		$label       = $this->db_fields['label'];
		$id          = $this->db_fields['id'];
		$meta        = $this->meta;
		$field       = $this->field;
		$field_class = RW_Meta_Box::get_class_name( $field );
		$attributes  = call_user_func( array( $field_class, 'get_attributes' ), $field, $object->$id );

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
	 * @param object $page   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() )
	{
		$output .= "</li>";
	}
}
