<?php
/**
 * This class handles getting and saving the updater option.
 *
 * @package Meta Box
 */

/**
 * Meta Box Update Option class
 *
 * @package Meta Box
 */
class RWMB_Update_Option {
	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option = 'meta_box_updater';

	/**
	 * Get an option.
	 *
	 * @param string $name    Option name. Pass null to return the option array.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed Option value or option array.
	 */
	public function get( $name = null, $default = null ) {
		$option = $this->is_network_activated() ? get_site_option( $this->option, array() ) : get_option( $this->option, array() );

		return null === $name ? $option : ( isset( $option[ $name ] ) ? $option[ $name ] : $default );
	}

	/**
	 * Get the API key.
	 *
	 * @return string
	 */
	public function get_api_key() {
		return defined( 'META_BOX_KEY' ) ? META_BOX_KEY : $this->get( 'api_key' );
	}

	/**
	 * Get license status.
	 */
	public function get_license_status() {
		return $this->get_api_key() ? $this->get( 'status', 'active' ) : 'no_key';
	}

	/**
	 * Update the option array.
	 *
	 * @param array $option Option value.
	 */
	public function update( $option ) {
		$old_option = (array) $this->get();

		$option = array_merge( $old_option, $option );
		if ( $this->is_network_activated() ) {
			update_site_option( $this->option, $option );
		} else {
			update_option( $this->option, $option );
		}
	}

	/**
	 * Detect if the plugin is network activated in Multisite environment.
	 *
	 * @return bool
	 */
	public function is_network_activated() {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		return is_multisite() && is_plugin_active_for_network( 'meta-box/meta-box.php' );
	}
}
