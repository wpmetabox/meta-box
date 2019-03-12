<?php
/**
 * Select walker select fields.
 *
 * @package Meta Box
 */

/**
 * The select walker class.
 */
class RWMB_Walker_Select extends RWMB_Walker_Base {
	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @param object $object            The data object.
	 * @param int    $depth             Depth of the item.
	 * @param array  $args              An array of additional arguments.
	 * @param int    $current_object_id ID of the current item.
	 */
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$indent = str_repeat( '&nbsp;', $depth * 4 );

		$output .= sprintf(
			'<option value="%s" %s>%s%s</option>',
			esc_attr( $object->value ),
			selected( in_array( $object->value, $this->meta ), true, false ),
			$indent,
			esc_html( RWMB_Field::filter( 'choice_label', $object->label, $this->field, $object ) )
		);
	}
}
