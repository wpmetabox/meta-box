<?php
/**
 * String helper functions.
 *
 * @package Meta Box
 */

/**
 * String helper class.
 *
 * @package Meta Box
 */
class RWMB_Helpers_String {
	/**
	 * Convert a value to studly caps case.
	 *
	 * @param  string $value
	 * @return string
	 */
	public static function title_case( $text ) {
		$text = str_replace( array( '-', '_' ), ' ', $text );
		$text = ucwords( $text );
		$text = str_replace( ' ', '_', $text );

		return $text;
	}
}
