<?php
/**
 * String helper functions.
 */
class RWMB_Helpers_String {
	public static function title_case( string $text ): string {
		$text = str_replace( [ '-', '_' ], ' ', $text );
		$text = ucwords( $text );
		$text = str_replace( ' ', '_', $text );

		return $text;
	}

	/**
	 * Sanitize a meta box ID for HTML/admin use.
	 *
	 * WordPress 7.1 embeds meta box IDs into tooltip button markup passed to sprintf().
	 * Percent-encoded characters from sanitize_title() (CJK titles) break that and cause a fatal error.
	 *
	 * @param string $id              Candidate ID, or title when generating from title.
	 * @param string $fallback_source Stable source for a hash fallback (usually the title).
	 */
	public static function sanitize_id( string $id, string $fallback_source = '' ): string {
		$sanitized = sanitize_title( $id );
		if ( '' === $sanitized || str_contains( $sanitized, '%' ) ) {
			$source = '' !== $fallback_source ? $fallback_source : $id;
			return 'meta-box-' . substr( md5( $source ), 0, 8 );
		}

		return $sanitized;
	}
}
