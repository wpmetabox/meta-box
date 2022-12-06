<?php
/**
 * String helper functions.
 */
class RWMB_Helpers_String {
	public static function title_case( string $text ) : string {
		$text = str_replace( [ '-', '_' ], ' ', $text );
		$text = ucwords( $text );
		$text = str_replace( ' ', '_', $text );

		return $text;
	}
}
