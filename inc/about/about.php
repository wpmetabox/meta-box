<?php
/**
 * Add about page for the Meta Box plugin.
 */
class RWMB_About {
	/**
	 * The updater checker object.
	 *
	 * @var object
	 */
	private $update_checker;

	/**
	 * Constructor.
	 *
	 * @param object $update_checker The updater checker object.
	 */
	public function __construct( $update_checker ) {
		$this->update_checker = $update_checker;
	}

	public function init() {
		// Add links to about page in the plugin action links.
		add_filter( 'plugin_action_links_meta-box/meta-box.php', [ $this, 'plugin_links' ], 20 );

		// Add a shared top-level admin menu and Dashboard page. Use priority 5 to show Dashboard at the top.
		add_action( 'admin_menu', [ $this, 'add_menu' ], 5 );
		add_action( 'admin_menu', [ $this, 'add_submenu' ], 5 );

		// If no admin menu, then hide the About page.
		add_action( 'admin_head', [ $this, 'hide_page' ] );

		// Redirect to about page after activation.
		add_action( 'activated_plugin', [ $this, 'redirect' ], 10, 2 );
	}

	public function plugin_links( array $links ): array {
		$links[] = '<a href="' . esc_url( $this->get_menu_link() ) . '">' . esc_html__( 'About', 'meta-box' ) . '</a>';
		if ( ! $this->update_checker->has_extensions() ) {
			$links[] = '<a href="https://elu.to/mpp" style="color: #39b54a; font-weight: bold">' . esc_html__( 'Go Pro', 'meta-box' ) . '</a>';
		}
		return $links;
	}

	public function add_menu() {
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

	public function add_submenu() {
		$parent_menu = $this->has_menu() ? 'meta-box' : $this->get_parent_menu();
		$about       = add_submenu_page(
			$parent_menu,
			__( 'Welcome to Meta Box', 'meta-box' ),
			__( 'Dashboard', 'meta-box' ),
			'activate_plugins',
			'meta-box',
			[ $this, 'render' ]
		);
		add_action( "load-$about", [ $this, 'enqueue' ] );
	}

	public function hide_page() {
		remove_submenu_page( $this->get_parent_menu(), 'meta-box' );
	}

	public function render() {
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="about-wrap">
							<?php
							include __DIR__ . '/sections/welcome.php';
							include __DIR__ . '/sections/tabs.php';
							if ( $this->update_checker->has_extensions() ) {
								include __DIR__ . '/sections/getting-started-pro.php';
							} else {
								include __DIR__ . '/sections/getting-started.php';
							}
							include __DIR__ . '/sections/extensions.php';
							include __DIR__ . '/sections/support.php';
							do_action( 'rwmb_about_tabs_content' );
							?>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<?php
						include __DIR__ . '/sections/products.php';
						include __DIR__ . '/sections/review.php';
						if ( ! $this->update_checker->has_extensions() ) {
							include __DIR__ . '/sections/upgrade.php';
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function enqueue() {
		wp_enqueue_style( 'meta-box-about', RWMB_URL . 'inc/about/css/about.css', [], RWMB_VER );
		wp_enqueue_script( 'meta-box-about', RWMB_URL . 'inc/about/js/about.js', [ 'jquery' ], RWMB_VER, true );
	}

	/**
	 * Redirect to about page after Meta Box has been activated.
	 *
	 * @param string $plugin       Path to the main plugin file from plugins directory.
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
	 *                             or just the current site. Multisite only. Default is false.
	 */
	public function redirect( $plugin, $network_wide = false ) {
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
}
