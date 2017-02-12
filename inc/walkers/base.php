<?php
/**
 * Base walker.
 * Walkers must inherit this class and overwrite methods with its own.
 *
 * @package Meta Box
 */

/**
 * Base walker class.
 */
abstract class RWMB_Walker_Base extends Walker {
	/**
	 * Field data.
	 *
	 * @access public
	 * @var array
	 */
	public $field;

	/**
	 * Meta data.
	 *
	 * @access public
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
}
