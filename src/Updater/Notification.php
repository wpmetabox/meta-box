<?php
namespace MetaBox\Updater;

/**
 * This class notifies users to enter or update license key.
 */
class Notification {
	private $checker;
	private $option;

	/**
	 * Settings page URL.
	 *
	 * @var string
	 */
	private $settings_page;


	public function __construct( Checker $checker, Option $option ) {
		$this->checker = $checker;
		$this->option  = $option;

		$this->settings_page = $option->is_network_activated() ? network_admin_url( 'settings.php?page=meta-box-updater' ) : admin_url( 'admin.php?page=meta-box-updater' );
	}

	/**
	 * Add hooks to show admin notice.
	 */
	public function init() {
		if ( ! $this->checker->has_extensions() ) {
			return;
		}

		// Show update message on Plugins page.
		$extensions = $this->checker->get_extensions();
		foreach ( $extensions as $extension ) {
			$file = "{$extension}/{$extension}.php";
			add_action( "in_plugin_update_message-$file", [ $this, 'show_update_message' ], 10, 2 );
			add_filter( "plugin_action_links_$file", [ $this, 'plugin_links' ], 20 );
		}

		$admin_notices_hook = $this->option->is_network_activated() ? 'network_admin_notices' : 'admin_notices';
		add_action( $admin_notices_hook, [ $this, 'notify' ] );
	}

	public function notify() {
		$excluded_screens = [
			'meta-box_page_meta-box-updater',
			'settings_page_meta-box-updater-network',
			'meta-box_page_meta-box-aio',
		];
		$screen = get_current_screen();
		if ( in_array( $screen->id, $excluded_screens, true ) ) {
			return;
		}

		$messages = [
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'no_key'  => __( 'You have not set your Meta Box license key yet, which means you are missing out on automatic updates and support! Please <a href="%1$s">enter your license key</a> or <a href="%2$s" target="_blank">get a new one here</a>.', 'meta-box' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'invalid' => __( 'Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">update your license key</a> or <a href="%2$s" target="_blank">get a new one</a> to enable automatic updates.', 'meta-box' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'error'   => __( 'Your license key for Meta Box is <b>invalid</b>. Please <a href="%1$s">update your license key</a> or <a href="%2$s" target="_blank">get a new one</a> to enable automatic updates.', 'meta-box' ),
			// Translators: %3$s - URL to the My Account page.
			'expired' => __( 'Your license key for Meta Box is <b>expired</b>. Please <a href="%3$s" target="_blank">renew your license</a> to get automatic updates and premium support.', 'meta-box' ),
		];
		$status   = $this->option->get_license_status();
		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		echo '<div class="notice notice-warning is-dismissible"><p><span class="dashicons dashicons-warning" style="color: #f56e28"></span> ', wp_kses_post( sprintf( $messages[ $status ], $this->settings_page, 'https://elu.to/mnp', 'https://elu.to/mna' ) ), '</p></div>';
	}

	/**
	 * Show update message on Plugins page.
	 *
	 * @param  array  $plugin_data Plugin data.
	 * @param  object $response    Available plugin update data.
	 */
	public function show_update_message( $plugin_data, $response ) {
		// Users have an active license.
		if ( ! empty( $response->package ) ) {
			return;
		}

		$messages = [
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'no_key'  => __( 'Please <a href="%1$s">enter your license key</a> or <a href="%2$s" target="_blank">get a new one here</a>.', 'meta-box' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'invalid' => __( 'Your license key is <b>invalid</b>. Please <a href="%1$s">update your license key</a> or <a href="%2$s" target="_blank">get a new one here</a>.', 'meta-box' ),
			// Translators: %1$s - URL to the settings page, %2$s - URL to the pricing page.
			'error'   => __( 'Your license key is <b>invalid</b>. Please <a href="%1$s">update your license key</a> or <a href="%2$s" target="_blank">get a new one here</a>.', 'meta-box' ),
			// Translators: %3$s - URL to the My Account page.
			'expired' => __( 'Your license key is <b>expired</b>. Please <a href="%3$s" target="_blank">renew your license</a>.', 'meta-box' ),
		];
		$status   = $this->option->get_license_status();
		if ( ! isset( $messages[ $status ] ) ) {
			return;
		}

		echo '<br><span style="width: 26px; height: 20px; display: inline-block;">&nbsp;</span>' . wp_kses_post( sprintf( $messages[ $status ], $this->settings_page, 'https://elu.to/mnp', 'https://elu.to/mna' ) );
	}

	public function plugin_links( array $links ): array {
		$status = $this->option->get_license_status();
		if ( 'active' === $status ) {
			return $links;
		}

		$text    = 'no_key' === $status ? __( 'Activate License', 'meta-box' ) : __( 'Update License', 'meta-box' );
		$links[] = '<a href="' . esc_url( $this->settings_page ) . '" class="rwmb-activate-license" style="color: #39b54a; font-weight: bold">' . esc_html( $text ) . '</a>';

		return $links;
	}
}
