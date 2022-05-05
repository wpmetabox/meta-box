<?php
class RWMB_Update_Checker {
	private $api_url = 'https://metabox.io/wp-json/buse2/updater/';
	private $option;

	public function __construct( $option ) {
		$this->option = $option;
	}

	public function init() {
		add_action( 'init', [ $this, 'enable_update' ], 1 );
	}

	public function enable_update() {
		if ( $this->has_extensions() ) {
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_updates' ] );
			add_filter( 'plugins_api', [ $this, 'get_info' ], 10, 3 );
		}
	}

	public function has_extensions() {
		$extensions = $this->get_extensions();
		return ! empty( $extensions );
	}

	public function get_extensions() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$extensions = [
			'mb-admin-columns',
			'mb-blocks',
			'mb-core',
			'mb-custom-table',
			'mb-frontend-submission',
			'mb-revision',
			'mb-settings-page',
			'mb-term-meta',
			'mb-user-meta',
			'mb-user-profile',
			'mb-views',
			'meta-box-aio',
			'meta-box-builder',
			'meta-box-columns',
			'meta-box-conditional-logic',
			'meta-box-geolocation',
			'meta-box-group',
			'meta-box-include-exclude',
			'meta-box-show-hide',
			'meta-box-tabs',
			'meta-box-template',

			'mb-favorite-posts',
			'mb-testimonials',
			'mb-user-avatar',
		];
		$plugins    = get_plugins();
		$plugins    = array_map( 'dirname', array_keys( $plugins ) );

		return array_intersect( $extensions, $plugins );
	}

	/**
	 * Check plugin for updates
	 *
	 * @param object $data The plugin update data.
	 *
	 * @return mixed
	 */
	public function check_updates( $data ) {
		static $response = null;

		$request = rwmb_request();

		// Bypass embed plugins via TGMPA.
		if ( $request->get( 'tgmpa-update' ) || 'tgmpa-bulk-update' === $request->post( 'action' ) ) {
			return $data;
		}

		// Make sure to send remote request once.
		if ( null === $response ) {
			$response = $this->request( 'plugins' );
		}

		if ( empty( $response ) ) {
			return $data;
		}

		if ( empty( $data ) ) {
			$data = new stdClass;
		}
		if ( ! isset( $data->response ) ) {
			$data->response = [];
		}

		$plugins = array_filter( $response['data'], [ $this, 'has_update' ] );
		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin['package'] ) ) {
				$plugin['upgrade_notice'] = __( 'UPDATE UNAVAILABLE! Please enter a valid license key to enable automatic updates.', 'meta-box' );
			}

			$data->response[ $plugin['plugin'] ] = (object) $plugin;
		}

		$this->option->update( [
			'status'  => $response['status'],
			'plugins' => array_keys( $plugins ),
		] );

		return $data;
	}

	/**
	 * Get plugin information
	 *
	 * @param object $data   The plugin update data.
	 * @param string $action Request action.
	 * @param object $args   Extra parameters.
	 *
	 * @return mixed
	 */
	public function get_info( $data, $action, $args ) {
		$plugins = $this->option->get( 'plugins', [] );
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || ! in_array( $args->slug, $plugins, true ) ) {
			return $data;
		}

		$response = $this->request( 'plugin', [ 'product' => $args->slug ] );
		return $response ? (object) $response['data'] : $data;
	}

	public function request( $endpoint, $args = [] ) {
		$args = wp_parse_args( $args, [
			'key' => $this->option->get_api_key(),
			'url' => home_url(),
		] );
		$args = array_filter( $args );

		// Get from cache first.
		$data      = compact( 'endpoint', 'args' );
		$cache_key = 'meta_box_' . md5( serialize( $data ) );
		if ( $this->option->is_network_activated() ) {
			$cache = get_site_transient( $cache_key );
		} else {
			$cache = get_transient( $cache_key );
		}
		if ( $cache ) {
			return $cache;
		}

		$url      = $this->api_url . $endpoint;
		$request  = wp_remote_get( add_query_arg( $args, $url ) );
		$response = wp_remote_retrieve_body( $request );
		$response = json_decode( $response, true );

		// Cache requests.
		if ( $this->option->is_network_activated() ) {
			set_site_transient( $cache_key, $response, DAY_IN_SECONDS );
		} else {
			set_transient( $cache_key, $response, DAY_IN_SECONDS );
		}

		return $response;
	}

	private function has_update( $remote_plugin_data ) {
		$slug    = $remote_plugin_data['plugin'];
		$plugins = get_plugins();

		if ( empty( $plugins[ $slug ] ) ) {
			return false;
		}

		$plugin = $plugins[ $slug ];
		return version_compare( $plugin['Version'], $remote_plugin_data['new_version'], '<' );
	}
}
