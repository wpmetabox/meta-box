<?php
/**
 * Password field class.
 */
class RWMB_Password_Field extends RWMB_Text_Field
{
	/**
	 * Get the attributes for a field
	 *
	 * @param array $field
	 * @param mixed $value
	 *
	 * @return array
	 */
	static function get_attributes( $field, $value = null )
	{
		$attributes = parent::get_attributes( $field, $value );
		$attributes['type'] = 'password';

		return $attributes;
	}

	static function value( $new, $old, $post_id, $field )
	{
		if($new != $old){
			return wp_hash_password( parent::value( $new, $old, $post_id, $field ) );
		} else {
			return parent::value( $new, $old, $post_id, $field );
		}
	}
}
