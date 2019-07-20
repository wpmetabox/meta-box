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
	private static $api_url = 'https://metabox.io/index.php';

	/**
	 * The update option.
	 *
	 * @var string
	 */
	private static $option = 'meta_box_updater';

	/**
	 * Add hooks to check plugin updates.
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_updates' ) );
		add_filter( 'plugins_api', array( $this, 'get_info' ), 10, 3 );
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
			$plugins = self::request( 'action=check_updates' );
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

		$option            = self::get_option();
		$option['plugins'] = array_keys( $plugins );
		if ( is_multisite() ) {
			update_site_option( self::$option, $option );
		} else {
			update_option( self::$option, $option );
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

		$info = self::request(
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
	public static function request( $args = '' ) {
		// Add email and API key to the request params.
		$option = self::get_option();
		$args   = wp_parse_args( $args, $option );
		$args   = array_filter( $args );

		$request = wp_remote_post(
			self::$api_url,
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
	private static function get_option() {
		return is_multisite() ? get_site_option( self::$option, array() ) : get_option( self::$option, array() );
	}
}
