<?php
namespace MetaBox\Dashboard;

class Dashboard {
	private $upgradable  = true;
	private $has_actions = false;
	private $is_aio      = false;
	private $assets_url;

	public function __construct( $update_checker, $update_option ) {
		$this->upgradable  = $this->get_upgradable( $update_checker, $update_option );
		$this->has_actions = defined( 'META_BOX_LITE_DIR' ) || defined( 'META_BOX_AIO_DIR' );
		$this->is_aio      = defined( 'META_BOX_AIO_DIR' );
		$this->assets_url  = RWMB_URL . 'src/Dashboard/assets';

		$this->init();
	}

	private function get_upgradable( $update_checker, $update_option ): bool {
		if ( ! $update_checker || ! $update_option ) {
			return true;
		}

		if ( ! $update_checker->has_extensions() ) {
			return true;
		}

		return $update_option->get_license_status() !== 'active';
	}

	public function init(): void {
		// Add links to the Dashboard in the plugin action links.
		add_filter( 'plugin_action_links_meta-box/meta-box.php', [ $this, 'plugin_links' ], 20 );

		// Add a shared top-level admin menu and the Dashboard. Use priority 5 to show the Dashboard at the top.
		add_action( 'admin_menu', [ $this, 'add_menu' ], 5 );
		add_action( 'admin_menu', [ $this, 'add_submenu' ], 5 );

		// If no admin menu, then hide the Dashboard.
		add_action( 'admin_head', [ $this, 'hide_page' ] );

		// Redirect to the Dashboard after activation.
		add_action( 'activated_plugin', [ $this, 'redirect' ], 10, 2 );

		// Handle install & activate plugin.
		add_action( 'wp_ajax_mb_dashboard_plugin_action', [ $this, 'handle_plugin_action' ] );

		// Handle ajax to get RSS.
		add_action( 'wp_ajax_mb_dashboard_feed', [ $this, 'get_feed' ] );
	}

	public function plugin_links( array $links ): array {
		$links[] = '<a href="' . esc_url( $this->get_menu_link() ) . '">' . esc_html__( 'Dashboard', 'meta-box' ) . '</a>';
		if ( $this->upgradable ) {
			$links[] = '<a href="https://elu.to/mpp" style="color: #39b54a; font-weight: bold">' . esc_html__( 'Upgrade', 'meta-box' ) . '</a>';
		}
		return $links;
	}

	public function add_menu(): void {
		if ( ! $this->has_menu() ) {
			return;
		}
		add_menu_page(
			__( 'Meta Box', 'meta-box' ),
			__( 'Meta Box', 'meta-box' ),
			'activate_plugins',
			'meta-box',
			'__return_null',
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB2aWV3Qm94PSIxNjQuMzI4IDE0OS40NDEgNTMuNDcgNDIuNjYiIHdpZHRoPSI1My40NyIgaGVpZ2h0PSI0Mi42NiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cGF0aCBkPSJNIDIwNC42NjggMTc5LjM5MSBMIDIwNS40ODggMTYwLjU1MSBMIDIwNS4zMTggMTYwLjUyMSBMIDE5My44ODggMTkyLjEwMSBMIDE4OC4xNDggMTkyLjEwMSBMIDE3Ni43NzggMTYwLjY0MSBMIDE3Ni42MDggMTYwLjY2MSBMIDE3Ny40MjggMTc5LjM5MSBMIDE3Ny40MjggMTg2LjA5MSBMIDE4MS45OTggMTg2Ljk3MSBMIDE4MS45OTggMTkyLjEwMSBMIDE2NC4zMjggMTkyLjEwMSBMIDE2NC4zMjggMTg2Ljk3MSBMIDE2OC44NjggMTg2LjA5MSBMIDE2OC44NjggMTU1LjQ4MSBMIDE2NC4zMjggMTU0LjYwMSBMIDE2NC4zMjggMTQ5LjQ0MSBMIDE2OC44NjggMTQ5LjQ0MSBMIDE4MC4wMjggMTQ5LjQ0MSBMIDE5MC44OTggMTgwLjg4MSBMIDE5MS4wNzggMTgwLjg4MSBMIDIwMi4wMzggMTQ5LjQ0MSBMIDIxNy43OTggMTQ5LjQ0MSBMIDIxNy43OTggMTU0LjYwMSBMIDIxMy4yMjggMTU1LjQ4MSBMIDIxMy4yMjggMTg2LjA5MSBMIDIxNy43OTggMTg2Ljk3MSBMIDIxNy43OTggMTkyLjEwMSBMIDIwMC4xMjggMTkyLjEwMSBMIDIwMC4xMjggMTg2Ljk3MSBMIDIwNC42NjggMTg2LjA5MSBMIDIwNC42NjggMTc5LjM5MSBaIiBzdHlsZT0iZmlsbDogcmdiKDE1OCwgMTYzLCAxNjgpOyB3aGl0ZS1zcGFjZTogcHJlOyIvPgo8L3N2Zz4='
		);
	}

	public function add_submenu(): void {
		$parent_menu = $this->has_menu() ? 'meta-box' : $this->get_parent_menu();
		$about       = add_submenu_page(
			$parent_menu,
			__( 'Dashboard', 'meta-box' ),
			__( 'Dashboard', 'meta-box' ),
			'activate_plugins',
			'meta-box',
			[ $this, 'render' ]
		);
		add_action( "load-$about", [ $this, 'enqueue' ] );
	}

	public function hide_page(): void {
		remove_submenu_page( $this->get_parent_menu(), 'meta-box' );
	}

	public function render(): void {
		?>
		<div class="mb-dashboard">
			<?php include 'content.php'; ?>
		</div>
		<?php
	}

	public function enqueue(): void {
		wp_enqueue_style( 'meta-box-dashboard', "$this->assets_url/css/dashboard.css", [], filemtime( __DIR__ . '/assets/css/dashboard.css' ) );
		wp_enqueue_style( 'featherlight', "$this->assets_url/css/featherlight.min.css", [], '1.7.14' );
		wp_enqueue_script( 'featherlight', "$this->assets_url/js/featherlight.min.js", [ 'jquery' ], '1.7.14', true );
		wp_enqueue_script( 'meta-box-dashboard', "$this->assets_url/js/dashboard.js", [ 'featherlight' ], filemtime( __DIR__ . '/assets/js/dashboard.js' ), true );

		$campaign = 'meta_box';
		if ( defined( 'META_BOX_LITE_DIR' ) ) {
			$campaign = 'meta_box_lite';
		} elseif ( defined( 'META_BOX_AIO_DIR' ) ) {
			$campaign = 'meta_box_aio';
		}

		wp_localize_script( 'meta-box-dashboard', 'MBD', [
			'campaign' => $campaign,
			'nonces'   => [
				'plugin' => wp_create_nonce( 'plugin' ),
				'feed'   => wp_create_nonce( 'feed' ),
			],
		] );
	}

	/**
	 * Redirect to about page after Meta Box has been activated.
	 *
	 * @param string $plugin       Path to the main plugin file from plugins directory.
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
	 *                             or just the current site. Multisite only. Default is false.
	 */
	public function redirect( $plugin, $network_wide = false ): void {
		$is_cli           = 'cli' === php_sapi_name();
		$is_plugin        = 'meta-box/meta-box.php' === $plugin;
		$is_bulk_activate = 'activate-selected' === rwmb_request()->post( 'action' ) && count( rwmb_request()->post( 'checked' ) ) > 1;
		$is_doing_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( ! $is_plugin || $network_wide || $is_cli || $is_bulk_activate || $this->is_bundled() || $is_doing_ajax ) {
			return;
		}
		wp_safe_redirect( $this->get_menu_link() );
		die;
	}

	private function get_menu_link(): string {
		$menu = $this->has_menu() ? 'admin.php?page=meta-box' : $this->get_parent_menu() . '?page=meta-box';
		return admin_url( $menu );
	}

	private function get_parent_menu(): string {
		return 'plugins.php';
	}

	private function has_menu(): bool {
		return apply_filters( 'rwmb_admin_menu', false );
	}

	private function is_bundled(): bool {
		// @codingStandardsIgnoreLine
		foreach ( $_REQUEST as $key => $value ) {
			if ( str_contains( $key, 'tgmpa' ) || ( is_string( $value ) && str_contains( $value, 'tgmpa' ) ) ) {
				return true;
			}
		}
		return false;
	}

	private function get_plugin_status( string $slug ): array {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin  = "$slug/$slug.php";
		$plugins = get_plugins();

		if ( empty( $plugins[ $plugin ] ) ) {
			return [
				'action'     => 'install',
				'text'       => __( 'Install', 'meta-box' ),
				'processing' => __( 'Installing...', 'meta-box' ),
				'done'       => __( 'Active', 'meta-box' ),
			];
		}

		if ( ! is_plugin_active( $plugin ) ) {
			return [
				'action'     => 'activate',
				'text'       => __( 'Activate', 'meta-box' ),
				'processing' => __( 'Activating...', 'meta-box' ),
				'done'       => __( 'Active', 'meta-box' ),
			];
		}

		return [
			'action'     => '',
			'text'       => __( 'Active', 'meta-box' ),
			'processing' => '',
			'done'       => '',
		];
	}

	public function handle_plugin_action(): void {
		check_ajax_referer( 'plugin' );

		$plugin = isset( $_GET['mb_plugin'] ) ? sanitize_text_field( wp_unslash( $_GET['mb_plugin'] ) ) : '';
		$action = isset( $_GET['mb_action'] ) ? sanitize_text_field( wp_unslash( $_GET['mb_action'] ) ) : '';

		if ( ! $plugin || ! $action || ! in_array( $action, [ 'install', 'activate' ], true ) ) {
			wp_send_json_error();
		}

		if ( $action === 'install' ) {
			$this->install_plugin( $plugin );
			$this->activate_plugin( $plugin );
		} elseif ( $action === 'activate' ) {
			$this->activate_plugin( $plugin );
		}

		wp_send_json_success();
	}

	private function install_plugin( string $slug ): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$plugin  = "$slug/$slug.php";
		$plugins = get_plugins();

		if ( isset( $plugins[ $plugin ] ) ) {
			return;
		}

		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'short_description' => false,
					'requires'          => false,
					'sections'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				],
			]
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		$skin     = new \Plugin_Installer_Skin( [ 'api' => $api ] );
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( ! $result ) {
			wp_send_json_error( __( 'Error installing plugin. Please try again.', 'meta-box' ) );
		}
	}

	private function activate_plugin( string $slug ): void {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$result = activate_plugin( "$slug/$slug.php", '', false, true );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
	}

	public function get_feed(): void {
		check_ajax_referer( 'feed' );

		$rss = fetch_feed( 'https://feeds.feedburner.com/metaboxio' );

		if ( is_wp_error( $rss ) ) {
			wp_send_json_error( $rss->get_error_message() );
		}

		$rss->set_item_limit( 10 );
		$items = $rss->get_items( 0, 10 );

		if ( ! $items ) {
			wp_send_json_error( __( 'No items available', 'meta-box' ) );
		}

		$items = array_map( function ( $item ): array {
			return [
				'url'         => $item->get_permalink(),
				'title'       => $item->get_title(),
				'description' => $item->get_description(),
				'content'     => $item->get_content(),
				'date'        => $item->get_date( get_option( 'date_format' ) ),
				'timestamp'   => $item->get_date( 'U' ),
			];
		}, $items );

		wp_send_json_success( $items );
	}
}
