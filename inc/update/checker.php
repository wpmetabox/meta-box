<?php
/**
 * The main update logic for updating Meta Box extensions.
 *
 * @package Meta Box
 */

/**
 * The update checker class for Meta Box extensions
 *
 * @package Meta Box
 */
class RWMB_Update_Checker {
	/**
	 * Update API endpoint URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://metabox.io/index.php';

	/**
	 * The update option object.
	 *
	 * @var object
	 */
	private $option;

	/**
	 * Constructor.
	 *
	 * @param object $option  Update option object.
	 */
	public function __construct( $option ) {
		$this->option = $option;
	}

	/**
	 * Add hooks to check plugin updates.
	 */
	public function init() {
		add_action( 'init', array( $this, 'enable_update' ), 1 );
	}

	/**
	 * Enable update checker when premium extensions are installed.
	 */
	public function enable_update() {
		if ( $this->has_extensions() ) {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_updates' ) );
			add_filter( 'plugins_api', array( $this, 'get_info' ), 10, 3 );
		}
	}

	/**
	 * Check if any premium extension is installed.
	 *
	 * @return bool
	 */
	public function has_extensions() {
		$extensions = $this->get_extensions();
		return ! empty( $extensions );
	}

	/**
	 * Get installed premium extensions.
	 *
	 * @return array Array of extension slugs.
	 */
	public function get_extensions() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$extensions = array(
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
		);
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
			$response = $this->request( array( 'action' => 'check_updates' ) );
		}

		if ( false === $response ) {
			return $data;
		}

		if ( empty( $data ) ) {
			$data = new stdClass;
		}
		if ( ! isset( $data->response ) ) {
			$data->response = array();
		}

		$plugins = array_filter( $response['data'], array( $this, 'has_update' ) );
		foreach ( $plugins as $plugin ) {
			if ( empty( $plugin->package ) ) {
				$plugin->upgrade_notice = __( 'UPDATE UNAVAILABLE! Please enter a valid license key to enable automatic updates.', 'meta-box' );
			}

			$data->response[ $plugin->plugin ] = $plugin;
		}

		$this->option->update(
			array(
				'status'  => $response['status'],
				'plugins' => array_keys( $plugins ),
			)
		);

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
		$plugins = $this->option->get( 'plugins', array() );
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || ! in_array( $args->slug, $plugins, true ) ) {
			return $data;
		}

		$response = $this->request(
			array(
				'action'  => 'get_info',
				'product' => $args->slug,
			)
		);

		return false === $response ? $data : $response['data'];
	}

	/**
	 * Send request to remote host
	 *
	 * @param array|string $args Query arguments.
	 *
	 * @return mixed
	 */
	public function request( $args = '' ) {
		$args = wp_parse_args(
			$args,
			array(
				'api_key' => $this->option->get_api_key(),
				'url'     => home_url(),
			)
		);
		$args = array_filter( $args );

		$cache_key = 'meta_box_' . md5( serialize( $args ) );
		if ( $this->option->is_network_activated() ) {
			$cache = get_site_transient( $cache_key );
		} else {
			$cache = get_transient( $cache_key );
		}
		if ( $cache ) {
			return $cache;
		}

		$request = wp_remote_post(
			$this->api_url,
			array(
				'body' => $args,
			)
		);

		$response = wp_remote_retrieve_body( $request );
		$response = $response ? @unserialize( $response ) : false;
		if ( $this->option->is_network_activated() ) {
			set_site_transient( $cache_key, $response, DAY_IN_SECONDS );
		} else {
			set_transient( $cache_key, $response, DAY_IN_SECONDS );
		}

		return $response;
	}

	/**
	 * Check if a plugin has an update to a new version.
	 *
	 * @param object $plugin_data The plugin update data.
	 *
	 * @return bool
	 */
	private function has_update( $plugin_data ) {
		$plugins = get_plugins();

		return isset( $plugins[ $plugin_data->plugin ] ) && version_compare( $plugins[ $plugin_data->plugin ]['Version'], $plugin_data->new_version, '<' );
	}
}
