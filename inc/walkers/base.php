<?php
/**
 * Base Walker
 * Walkers must inherit this class and overwrite methods with its own.
 */
abstract class RWMB_Walker_Base extends Walker
{
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

	function __construct( $db_fields, $field, $meta )
	{
		$this->db_fields = wp_parse_args( (array) $db_fields, array(
			'parent' => '',
			'id'     => '',
			'label'  => '',
		) );
		$this->field     = $field;
		$this->meta      = (array) $meta;
	}
}
