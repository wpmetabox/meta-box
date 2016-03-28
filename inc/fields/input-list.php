<?php
/**
 * Input list field.
 */
class RWMB_Input_List_Field extends RWMB_Choice_Field
{
	/**
	 * Enqueue scripts and styles
	 */
	public static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'rwmb-input-list', RWMB_CSS_URL . 'input-list.css', array(), RWMB_VER );
		wp_enqueue_script( 'rwmb-input-list', RWMB_JS_URL . 'input-list.js', array(), RWMB_VER, true );
	}

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
	public static function walk( $options, $db_fields, $meta, $field )
	{
		$walker = new RWMB_Input_List_Walker( $db_fields, $field, $meta );
		$output = sprintf( '<ul class="rwmb-input-list %s">', $field['collapse'] ? 'collapse' : '' );
		$output .= $walker->walk( $options, $field['flatten'] ? - 1 : 0 );
		$output .= '</ul>';

		return $output;
	}

	/**
	 * Normalize parameters for field
	 *
	 * @param array $field
	 * @return array
	 */
	public static function normalize( $field )
	{
		$field = $field['multiple'] ? RWMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = RWMB_Input_Field::normalize( $field );
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, array(
			'collapse' => true,
		) );

		$field['flatten'] = $field['multiple'] ? $field['flatten'] : true;

		return $field;
	}

	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null )
	{
		$attributes          = RWMB_Input_Field::get_attributes( $field, $value );
		$attributes['id']    = false;
		$attributes['type']  = $field['multiple'] ? 'checkbox' : 'radio';
		$attributes['value'] = $value;

		return $attributes;
	}

	/**
	 * Output the field value
	 * Display option name instead of option value
	 *
	 * @use self::meta()
	 *
	 * @param  array    $field   Field parameters
	 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Field value
	 */
	public static function the_value( $field, $args = array(), $post_id = null )
	{
		$value = parent::get_value( $field, $args, $post_id );
		return empty( $value ) ? '' : $field['options'][$value];
	}
}
