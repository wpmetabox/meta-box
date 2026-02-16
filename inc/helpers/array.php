<?php
defined( 'ABSPATH' ) || die;

use MetaBox\Support\Arr;

/**
 * No longer needed. Keep it here for backward compatibility.
 */
class RWMB_Helpers_Array extends Arr {

	/**
	 * array_filter_recursive
	 * @link https://gist.github.com/benjamw/1690140
	 * @param array
	 * @param string optional callback function name
	 * @param bool optional flag removal of empty arrays after filtering
	 * @return array merged array
	 */
	public static function array_filter_recursive( array $array, callable $callback = null, bool $remove_empty_arrays = false ): array {
		foreach ( $array as $key => &$value ) { // mind the reference
			if ( is_array( $value ) ) {
				$value = call_user_func_array( [ 'self', 'array_filter_recursive' ], array( $value, $callback, $remove_empty_arrays ) );
				if ( $remove_empty_arrays && ! (bool) $value ) {
					unset( $array[ $key ] );
				}
			} elseif ( ! is_null( $callback ) && ! $callback( $value ) ) {
				unset( $array[ $key ] );
			} elseif ( ! (bool) $value ) {
				unset( $array[ $key ] );
			}
		}
		unset( $value ); // kill the reference

		return array_filter( $array );
	}
}
