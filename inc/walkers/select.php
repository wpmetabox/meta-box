<?php

defined( 'ABSPATH' ) || die;

/**
 * Select walker select fields.
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
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		$indent = str_repeat( '&nbsp;', $depth * 4 );

		$attrs = sprintf(
			'value="%s" %s',
			esc_attr( $object->value ),
			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			selected( in_array( $object->value, $this->meta ), true, false )
		);

		if ( ! empty( $object->thumbnail ) ) {
			$attrs .= ' data-thumbnail="' . esc_url( $object->thumbnail ) . '"';
		}

		$output .= sprintf(
			'<option %s>%s%s</option>',
			$attrs,
			$indent,
			esc_html( $object->label )
		);
	}
}
