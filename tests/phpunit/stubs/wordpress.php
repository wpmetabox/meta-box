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

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_slash' ) ) {
	function wp_slash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_slash', $value );
		}
		return is_string( $value ) ? addslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = -1 ): string {
		return 'nonce-' . md5( (string) $action );
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action = -1 ) {
		return $nonce === wp_create_nonce( $action ) ? 1 : false;
	}
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( $action = -1, $query_arg = false, $stop = true ) {
		$query_arg = $query_arg ? $query_arg : '_wpnonce';
		$nonce     = isset( $_REQUEST[ $query_arg ] ) ? wp_unslash( $_REQUEST[ $query_arg ] ) : '';

		if ( wp_verify_nonce( $nonce, $action ) ) {
			return 1;
		}
		if ( $stop ) {
			throw new RuntimeException( 'Invalid Ajax nonce.', 403 );
		}
		return false;
	}
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
	function wp_send_json_error( $data = null, $status_code = null ) {
		throw new RuntimeException( 'wp_send_json_error', (int) $status_code );
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '', $title = '', $args = [] ) {
		if ( is_int( $title ) ) {
			$args = [ 'response' => $title ];
		}
		$status = is_array( $args ) ? (int) ( $args['response'] ?? 0 ) : (int) $args;
		throw new RuntimeException( (string) $message, $status );
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
