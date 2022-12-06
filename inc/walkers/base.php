<?php
/**
 * Base walker.
 * Walkers must inherit this class and overwrite methods with its own.
 */
abstract class RWMB_Walker_Base extends Walker {
	/**
	 * Field settings.
	 *
	 * @var array
	 */
	public $field;

	/**
	 * Field meta data.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Constructor.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $meta  Meta value.
	 */
	public function __construct( $field, $meta ) {
		$this->db_fields = [
			'id'     => 'value',
			'parent' => 'parent',
		];

		$this->field = $field;
		$this->meta  = (array) $meta;
	}
}
