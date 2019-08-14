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
		$option = is_multisite() ? get_site_option( $this->option, array() ) : get_option( $this->option, array() );

		return null === $name ? $option : ( isset( $option[ $name ] ) ? $option[ $name ] : $default );
	}

	/**
	 * Update the option array.
	 *
	 * @param array $option Option value.
	 */
	public function update( $option ) {
		$old_option = (array) $this->get();

		$option = array_merge( $old_option, $option );
		if ( is_multisite() ) {
			update_site_option( $this->option, $option );
		} else {
			update_option( $this->option, $option );
		}
	}
}
