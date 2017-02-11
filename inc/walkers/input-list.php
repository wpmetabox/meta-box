<?php
/**
 * The input list walker for checkbox and radio list fields.
 *
 * @package Meta Box
 */

/**
 * The input list walker class.
 */
class RWMB_Walker_Input_List extends RWMB_Walker_Base {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '<ul class="rwmb-input-list">';
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '</ul>';
	}

	/**
	 * Start the element output.
	 *
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @param object $object            The data object.
	 * @param int    $depth             Depth of the item.
	 * @param array  $args              An array of additional arguments.
	 * @param int    $current_object_id ID of the current item.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$label      = $this->db_fields['label'];
		$id         = $this->db_fields['id'];
		$attributes = RWMB_Field::call( 'get_attributes', $this->field, $object->$id );

		$output .= sprintf(
			'<li><label><input %s %s>%s</label>',
			RWMB_Field::render_attributes( $attributes ),
			checked( in_array( $object->$id, $this->meta ), 1, false ),
			RWMB_Field::filter( 'choice_label', $object->$label, $this->field, $object )
		);
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object The data object.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_el( &$output, $object, $depth = 0, $args = array() ) {
		$output .= '</li>';
	}
}
