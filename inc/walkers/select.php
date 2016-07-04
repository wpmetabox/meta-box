<?php
/**
 * Select Walker
 * For generating Select fields
 */
class RWMB_Walker_Select extends RWMB_Walker_Base
{
	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @param object $object            Item
	 * @param int    $depth             Depth of Item.
	 * @param int    $current_object_id Item id.
	 * @param array  $args
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 )
	{
		$label  = $this->db_fields['label'];
		$id     = $this->db_fields['id'];
		$meta   = $this->meta;
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
