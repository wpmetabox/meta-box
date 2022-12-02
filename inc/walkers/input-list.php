<?php
/**
 * The input list walker for checkbox and radio list fields.
 */
class RWMB_Walker_Input_List extends RWMB_Walker_Base {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$output .= '<fieldset class="rwmb-input-list">';
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$output .= '</fieldset>';
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
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		$attributes = RWMB_Field::call( 'get_attributes', $this->field, $object->value );

		$output .= sprintf(
			'<label><input %s %s>%s</label>',
			RWMB_Field::render_attributes( $attributes ),
			checked( in_array( $object->value, $this->meta ), true, false ),
			$object->label
		);
	}
}
