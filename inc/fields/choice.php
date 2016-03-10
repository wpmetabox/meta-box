<?php

abstract class RWMB_Choice_Field extends RWMB_Field
{
  /**
   * Walk options
   *
   * @param mixed $meta
   * @param array $field
   * @param mixed $options
   * @param mixed $db_fields
   *
   * @return string
   */
  static function walk( $options, $db_fields, $meta, $field )
  {
    return '';
  }

  /**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'flatten'    => true,
      'options'    => array(),
		) );

    $options = array();
    foreach( (array) $field['options'] as $value => $label )
    {
      $option = is_array( $label ) ? $label : array( 'label' => (string) $label, 'value' => (string) $value );
      if( isset( $option['label'] ) && isset( $option['value'] ) )
        $options[] = $option;
    }

    $field['options'] = $options;

		return $field;
	}

  /**
	 * Get field names of object to be used by walker
	 *
	 * @return array
	 */
	static function get_db_fields()
	{
		return array(
			'parent' => 'parent',
			'id'     => 'value',
			'label'  => 'label',
		);
	}

  /**
	 * Get options for walker
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	static function get_options( $field )
	{
    $options = array();
    foreach( $field['options'] as $option )
    {
        $options[] = (object) $option;
    }
		return $options;
	}
}
