<?php
namespace MetaBox;

class FeaturedPlugins {
	public function __construct() {
		add_filter( 'plugins_api_result', [ $this, 'process' ], 10, 2 );
	}

	public function process( $result, $action ) {
		global $tab;
		if ( ! in_array( $tab, [ 'featured', 'recommended' ], true ) ) {
			return $result;
		}

		if ( is_wp_error( $result ) || $action !== 'query_plugins' ) {
			return $result;
		}

		if ( ! apply_filters( 'rwmb_modify_plugin_recommendations', true ) ) {
			return $result;
		}

		foreach ( $result->plugins as $index => $plugin ) {
			if ( $plugin['slug'] === 'secure-custom-fields' ) {
				unset( $result->plugins[ $index ] );
			}
		}

		$result->plugins = array_values( $result->plugins );

		return $result;
	}
}
