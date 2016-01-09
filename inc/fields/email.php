<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

class RWMB_Email_Field extends RWMB_Text_Field
{
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

		return $field;
	}
	
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['type'] = 'email';	
		return $attributes;
	}

	/**
	 * Sanitize email
	 *
	 * @param mixed $new
	 * @param mixed $old
	 * @param int   $post_id
	 * @param array $field
	 *
	 * @return string
	 */
	static function value( $new, $old, $post_id, $field )
	{
		if ( $field['clone'] )
		{
			$new = (array) $new;
			$new = array_map( 'sanitize_email', $new );
		}
		else
		{
			$new = sanitize_email( $new );
		}

		return $new;
	}
}
