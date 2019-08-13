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
	 */
	public function get( $name = null, $default = null ) {
		$option = is_multisite() ? get_site_option( $this->option ) : get_option( $this->option );

		if ( null === $option ) {
			return $option;
		}

		return isset( $option[ $name ] ) ? $option[ $name ] : $default;
	}

	/**
	 * Set an option.
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 */
	public function set( $name, $value ) {
		$option          = $this->get();
		$option[ $name ] = $value;

		$this->update( $option );
	}

	/**
	 * Update the option array.
	 *
	 * @param array $option Option value.
	 */
	public function update( $option ) {
		if ( is_multisite() ) {
			update_site_option( $this->option, $option );
		} else {
			update_option( $this->option, $option );
		}
	}
}
