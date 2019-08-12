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
		$messages = array(
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'no_key'  => __( '<b>Warning!</b> You have not set your Meta Box license key yet, which means you are missing out on automatic updates and support! <a href="%1$s">Enter your license key</a> or <a href="%2$s" target="_blank">get one here</a>.', 'meta-box-updater' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'invalid' => __( '<b>Warning!</b> Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">fix it</a> or <a href="%2$s" target="_blank">get one here</a> to get automatic updates and premium support.', 'meta-box-updater' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'error'   => __( '<b>Warning!</b> Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">fix it</a> or <a href="%2$s" target="_blank">get one here</a> to get automatic updates and premium support.', 'meta-box-updater' ),
			// Translators: %3$s - URL to the My Account page.
			'expired' => __( '<b>Warning!</b> Your license key for Meta Box is <b>expired</b>. Please <a href="%3$s" target="_blank">renew here</a> to get automatic updates and premium support.', 'meta-box-updater' ),
		);
		$status   = $this->get_license_status();
		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		$admin_url = is_multisite() ? network_admin_url( "settings.php?page={$this->page_id}" ) : admin_url( "admin.php?page={$this->page_id}" );
		echo '<div class="notice notice-warning"><p>', wp_kses_post( sprintf( $messages[ $status ], $admin_url, 'https://metabox.io/pricing/', 'https://metabox.io/my-account/' ) ), '</p></div>';
	}

	/**
	 * Get license status.
	 */
	public function get_license_status() {
		if ( ! $this->checker->get_api_key() ) {
			return 'no_key';
		}
		$option = is_multisite() ? get_site_option( $this->option ) : get_option( $this->option );
		return isset( $option['status'] ) ? $option['status'] : 'active';
	}
}
