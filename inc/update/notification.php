<?php
/**
 * This class notifies users to enter or update license key.
 *
 * @package Meta Box
 */

/**
 * Meta Box Update Notification class
 *
 * @package Meta Box
 */
class RWMB_Update_Notification {
	/**
	 * Update option.
	 *
	 * @var string
	 */
	private $option = 'meta_box_updater';

	/**
	 * Settings page ID.
	 *
	 * @var string
	 */
	private $page_id = 'meta-box-updater';

	/**
	 * The update checker object
	 *
	 * @var object
	 */
	private $checker;

	/**
	 * Constructor.
	 *
	 * @param object $checker Update checker object.
	 */
	public function __construct( $checker ) {
		$this->checker = $checker;
	}

	/**
	 * Add hooks to create the settings page and show admin notice.
	 */
	public function init() {
		$admin_notices_hook = is_multisite() ? 'network_admin_notices' : 'admin_notices';
		add_action( $admin_notices_hook, array( $this, 'notify' ) );
	}

	/**
	 * Notify users to enter license key.
	 */
	public function notify() {
		if ( ! $this->checker->has_extensions() ) {
			return;
		}
		$messages  = array(
			// Translators: %1$s - URL to Meta Box Updater settings page, %2$s - URL to MetaBox.io website.
			'no_key'  => __( '<b>Warning!</b> You have not set your Meta Box license key yet, which means you are missing out on automatic updates and support! <a href="%1$s">Enter your license key</a> or <a href="%2$s" target="_blank">get one here</a>.', 'meta-box-updater' ),
			// Translators: %1$s - URL to Meta Box Updater settings page, %2$s - URL to MetaBox.io website.
			'invalid' => __( '<b>Warning!</b> Your license key for Meta Box is invalid or expired. Please <a href="%1$s">fix it</a> or <a href="%2$s" target="_blank">renew</a> to receive automatic updates and premium support.', 'meta-box-updater' ),
		);
		$status    = $this->get_license_status();
		$admin_url = is_multisite() ? network_admin_url( "settings.php?page={$this->page_id}" ) : admin_url( "admin.php?page={$this->page_id}" );
		if ( isset( $messages[ $status ] ) ) {
			echo '<div class="notice notice-warning"><p>', wp_kses_post( sprintf( $messages[ $status ], $admin_url, 'https://metabox.io/pricing/' ) ), '</p></div>';
		}
	}

	/**
	 * Get license status.
	 */
	public function get_license_status() {
		$option = is_multisite() ? get_site_option( $this->option ) : get_option( $this->option );
		if ( empty( $option['api_key'] ) ) {
			return 'no_key';
		}
		if ( isset( $option['status'] ) && 'success' !== $option['status'] ) {
			return 'invalid';
		}
		return 'valid';
	}
}
