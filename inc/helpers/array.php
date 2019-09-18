<?php
/**
 * Array helper functions.
 *
 * @package Meta Box
 */

/**
 * Array helper class.
 *
 * @package Meta Box
 */
class RWMB_Helpers_Array {
	/**
	 * New array map function that accepts more params than just values.
	 * Params: array|item, callback, other params.
	 *
	 * @return array
	 */
	public static function map() {
		$args     = func_get_args();
		$items    = array_shift( $args );
		$callback = array_shift( $args );

		if ( ! is_array( $items ) ) {
			array_unshift( $args, $items );
			return call_user_func_array( $callback, $args );
		}

		return array_map(
			function( $item ) use ( $callback, $args ) {
				array_unshift( $args, $item );
				return call_user_func_array( $callback, $args );
			},
			$items
		);
	}

	/**
	 * Convert a comma separated string to array.
	 *
	 * @param string $csv Comma separated string.
	 * @return array
	 */
	public static function from_csv( $csv ) {
		return is_array( $csv ) ? $csv : array_filter( array_map( 'trim', explode( ',', $csv . ',' ) ) );
	}

	/**
	 * Change array key.
	 *
	 * @param  array  $array Input array.
	 * @param  string $from  From key.
	 * @param  string $to    To key.
	 */
	public static function change_key( &$array, $from, $to ) {
		if ( isset( $array[ $from ] ) ) {
			$array[ $to ] = $array[ $from ];
		}
		unset( $array[ $from ] );
	}

	/**
	 * Flatten an array.
	 *
	 * @link https://stackoverflow.com/a/1320156/371240
	 *
	 * @param  array $array Input array.
	 * @return array
	 */
	public static function flatten( $array ) {
		$return = array();
		array_walk_recursive(
			$array,
			function( $a ) use ( &$return ) {
				$return[] = $a;
			}
		);
		return $return;
	}
}
