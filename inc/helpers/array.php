<?php
/**
 * Array helper functions.
 */
class RWMB_Helpers_Array {
	/**
	 * New array map function that accepts more params than just values.
	 * Params: array|item, callback, other params.
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
	 * @param string|array $csv Comma separated string.
	 */
	public static function from_csv( $csv ) : array {
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
	 * @link https://stackoverflow.com/a/1320156/371240
	 */
	public static function flatten( array $array ) : array {
		$return = [];
		array_walk_recursive(
			$array,
			function( $a ) use ( &$return ) {
				$return[] = $a;
			}
		);
		return $return;
	}

	/**
	 * Ensure a variable is an array.
	 * @param  mixed $input Input value.
	 */
	public static function ensure( $input ) : array {
		return (array) $input;
	}
}
