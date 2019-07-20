<?php
/**
 * The main update logic for updating Meta Box extensions.
 *
 * @package Meta Box
 */

/**
 * The updater class for Meta Box extensions
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
	 * The update option.
	 *
	 * @var string
	 */
	private $option = 'meta_box_updater';

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
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$extensions = array(
			'mb-admin-columns',
			'mb-blocks',
			'mb-custom-table',
			'mb-frontend-submission',
			'mb-revision',
			'mb-settings-page',
			'mb-term-meta',
			'mb-user-meta',
			'mb-user-profile',
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
		);
		$plugins = get_plugins();
		$plugins = array_map( 'dirname', array_keys( $plugins ) );

		$installed_extensions = array_intersect( $extensions, $plugins );

		return ! empty( $installed_extensions );
	}

	/**
	 * Check plugin for updates
	 *
	 * @param object $data The plugin update data.
	 *
	 * @return mixed
	 */
	public function check_updates( $data ) {
		static $plugins = null;

		// Make sure to send remote request once.
		if ( null === $plugins ) {
			$plugins = $this->request( 'action=check_updates' );
		}

		if ( false === $plugins ) {
			return $data;
		}

		if ( ! isset( $data->response ) ) {
			$data->response = array();
		}

		$plugins = array_filter( $plugins, array( $this, 'has_update' ) );
		foreach ( $plugins as $plugin ) {
			$data->response[ $plugin->plugin ] = $plugin;
		}

		$option            = $this->get_option();
		$option['plugins'] = array_keys( $plugins );
		if ( is_multisite() ) {
			update_site_option( $this->option, $option );
		} else {
			update_option( $this->option, $option );
		}

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
		$option  = $this->get_option();
		$plugins = isset( $option['plugins'] ) ? $option['plugins'] : array();
		if ( 'plugin_information' !== $action || ! isset( $args->slug ) || ! in_array( $args->slug, $plugins, true ) ) {
			return $data;
		}

		$info = $this->request(
			array(
				'action'  => 'get_info',
				'product' => $args->slug,
			)
		);

		return false === $info ? $data : $info;
	}

	/**
	 * Send request to remote host
	 *
	 * @param array|string $args Query arguments.
	 *
	 * @return mixed
	 */
	public function request( $args = '' ) {
		// Add email and API key to the request params.
		$option = $this->get_option();
		$args   = wp_parse_args( $args, $option );
		$args   = array_filter( $args );

		$request = wp_remote_post(
			$this->api_url,
			array(
				'body' => $args,
			)
		);

		$response = wp_remote_retrieve_body( $request );
		if ( $response ) {
			$data = @unserialize( $response );

			return $data;
		}

		return false;
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

	/**
	 * Get update option.
	 *
	 * @return array
	 */
	private function get_option() {
		return is_multisite() ? get_site_option( $this->option, array() ) : get_option( $this->option, array() );
	}
}
