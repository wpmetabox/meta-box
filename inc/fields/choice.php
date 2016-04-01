<?php
/**
 * Abstract class for any kind of choice field.
 */
abstract class RWMB_Choice_Field extends RWMB_Field
{
	/**
	 * Walk options
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @param mixed $options
	 * @param mixed $db_fields
	 * @return string
	 */
	public static function walk( $options, $db_fields, $meta, $field )
	{
		return '';
	}

	/**
	 * Get field HTML
	 *
	 * @param mixed $meta
	 * @param array $field
	 * @return string
	 */
	public static function html( $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$meta        = (array) $meta;
		$options     = call_user_func( array( $field_class, 'get_options' ), $field );
		$db_fields   = call_user_func( array( $field_class, 'get_db_fields' ), $field );

		return call_user_func( array( $field_class, 'walk' ), $options, $db_fields, $meta, $field );
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	public static function normalize( $field )
	{
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'flatten' => true,
			'options' => array(),
		) );

		return $field;
	}

	/**
	 * Get field names of object to be used by walker
	 *
	 * @return array
	 */
	public static function get_db_fields()
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
	public static function get_options( $field )
	{
		$options = array();
		foreach ( (array) $field['options'] as $value => $label )
		{
			$option = is_array( $label ) ? $label : array( 'label' => (string) $label, 'value' => (string) $value );
			if ( isset( $option['label'] ) && isset( $option['value'] ) )
				$options[$option['value']] = (object) $option;
		}
		return $options;
	}

	/**
	 * Output the field value
	 * Display unordered list of option labels, not option values
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Not used for these fields.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string Link(s) to post
	 */
	public static function the_value( $field, $args = array(), $post_id = null )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$value       = call_user_func( array( $field_class, 'get_value' ), $field, $args, $post_id );

		if ( ! $value )
			return '';

		if ( $field['clone'] && $field['multiple'] )
		{
			$output = '<ul>';
			foreach ( $value as $subvalue )
			{
				$output .= '<li>';
				$output .= call_user_func( array( $field_class, 'list_option_labels' ), $subvalue, $field );
				$output .= '</li>';
			}
			$output .= '</ul>';
		}
		elseif ( $field['clone'] || $field['multiple'] )
		{
			$output = call_user_func( array( $field_class, 'list_option_labels' ), $value, $field );
		}
		else
		{
			$output = call_user_func( array( $field_class, 'get_option_label' ), $value, $field );
		}
		return $output;
	}

	/**
	 * List option labels
	 *
	 * @param array $meta
	 * @param array $field Field parameter
	 * @return string
	 */
	public static function list_option_labels( $meta, $field )
	{
		$field_class = RW_Meta_Box::get_class_name( $field );
		$output      = '<ul>';
		foreach ( $meta as $m )
		{
			$output .= sprintf( '<li>%s</li>', call_user_func( array( $field_class, 'get_option_label' ), $m, $field ) );
		}

		return $output . '</ul>';
	}

	/**
	 * Get option label
	 *
	 * @param string $value Option value
	 * @param array  $field Field parameter
	 *
	 * @return string
	 */
	public static function get_option_label( $value, $field )
	{
		$options = call_user_func( array( RW_Meta_Box::get_class_name( $field ), 'get_options' ), $field );
		return $options[$value]->label;
	}
}
