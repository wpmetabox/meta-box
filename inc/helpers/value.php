<?php
/**
 * Helper functions for checking values.
 *
 * @package Meta Box
 */

/**
 * Helper class for checking values.
 *
 * @package Meta Box
 */
class RWMB_Helpers_Value {
	/**
	 * Check if a value is valid for field (not empty "WordPress way"), e.g. equals to empty string or array.
	 *
	 * @param mixed $value Input value.
	 * @return bool
	 */
	public static function is_valid_for_field( $value ) {
		return '' !== $value && [] !== $value;
	}

	/**
	 * Check if a value is valid for attribute.
	 *
	 * @param mixed $value Input value.
	 * @return bool
	 */
	public static function is_valid_for_attribute( $value ) {
		return '' !== $value && false !== $value;
	}
}
