<?php
/**
 * Select tree walker for cascading select fields.
 *
 * @package Meta Box
 */

/**
 * The select tree walker class.
 */
class RWMB_Walker_Select_Tree {
	/**
	 * Field data.
	 *
	 * @var string
	 */
	public $field;

	/**
	 * Field meta value.
	 *
	 * @var array
	 */
	public $meta = array();

	/**
	 * Constructor.
	 *
	 * @param array $db_fields Database fields.
	 * @param array $field     Field parameters.
	 * @param mixed $meta      Meta value.
	 */
	public function __construct( $db_fields, $field, $meta ) {
		$this->db_fields = wp_parse_args( (array) $db_fields, array(
			'parent' => '',
			'id'     => '',
			'label'  => '',
		) );
		$this->field     = $field;
		$this->meta      = (array) $meta;
	}

	/**
	 * Display array of elements hierarchically.
	 *
	 * @param array $options An array of options.
	 *
	 * @return string
	 */
	public function walk( $options ) {
		$parent   = $this->db_fields['parent'];
		$children = array();

		foreach ( $options as $option ) {
			$index = isset( $option->$parent ) ? $option->$parent : 0;
			$children[ $index ][] = $option;
		}

		$top_level = isset( $children[0] ) ? 0 : $options[0]->$parent;
		return $this->display_level( $children, $top_level, true );
	}

	/**
	 * Display a hierarchy level.
	 *
	 * @param array $options   An array of options.
	 * @param int   $parent_id Parent item ID.
	 * @param bool  $active    Whether to show or hide.
	 *
	 * @return string
	 */
	public function display_level( $options, $parent_id = 0, $active = false ) {
		$id         = $this->db_fields['id'];
		$field      = $this->field;
		$walker     = new RWMB_Walker_Select( $this->db_fields, $field, $this->meta );
		$attributes = RWMB_Field::call( 'get_attributes', $field, $this->meta );

		$children = $options[ $parent_id ];
		$output   = sprintf(
			'<div class="rwmb-select-tree %s" data-parent-id="%s"><select %s>',
			$active ? '' : 'hidden',
			$parent_id,
			RWMB_Field::render_attributes( $attributes )
		);
		$output .= isset( $field['placeholder'] ) ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
		$output .= $walker->walk( $children, - 1 );
		$output .= '</select>';

		foreach ( $children as $c ) {
			if ( isset( $options[ $c->$id ] ) ) {
				$output .= $this->display_level( $options, $c->$id, in_array( $c->$id, $this->meta ) && $active );
			}
		}

		$output .= '</div>';
		return $output;
	}
}
