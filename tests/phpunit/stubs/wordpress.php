<?php
/**
 * Minimal WordPress stubs for PHPUnit when wp-load.php is unavailable (e.g. CI).
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	/**
	 * @param mixed $args
	 * @param array $defaults
	 */
	function wp_parse_args( $args, $defaults = [] ): array {
		if ( is_object( $args ) ) {
			$parsed = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed = $args;
		} else {
			parse_str( (string) $args, $parsed );
		}

		return array_merge( $defaults, $parsed );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ): string {
		$key = strtolower( (string) $key );
		return (string) preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '', $scheme = 'admin' ): string {
		return 'http://example.com/wp-admin/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = -1 ): string {
		return 'test-nonce';
	}
}

if ( ! function_exists( 'get_post_type_object' ) ) {
	function get_post_type_object( $post_type ) {
		return null;
	}
}

if ( ! function_exists( 'post_type_supports' ) ) {
	function post_type_supports( $post_type, $feature ): bool {
		return false;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook_name, $value, ...$args ) {
		return $value;
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $value ): string {
		return untrailingslashit( $value ) . '/';
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $value ): string {
		return rtrim( (string) $value, '/\\' );
	}
}
