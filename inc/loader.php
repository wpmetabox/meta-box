<?php
/**
 * Load plugin's files with check for installing it as a standalone plugin or
 * a module of a theme / plugin. If standalone plugin is already installed, it
 * will take higher priority.
 *
 * @package Meta Box
 */

/**
 * Plugin loader class.
 *
 * @package Meta Box
 */
class RWMB_Loader {
	/**
	 * Define plugin constants.
	 */
	protected function constants() {
		// Script version, used to add version for scripts and styles.
		define( 'RWMB_VER', '5.3.8' );

		list( $path, $url ) = self::get_path( dirname( dirname( __FILE__ ) ) );

		// Plugin URLs, for fast enqueuing scripts and styles.
		define( 'RWMB_URL', $url );
		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		// Plugin paths, for including files.
		define( 'RWMB_DIR', $path );
		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
	}

	/**
	 * Get plugin base path and URL.
	 * The method is static and can be used in extensions.
	 *
	 * @link http://www.deluxeblogtips.com/2013/07/get-url-of-php-file-in-wordpress.html
	 * @param string $path Base folder path.
	 * @return array Path and URL.
	 */
	public static function get_path( $path = '' ) {
		// Plugin base path.
		$path       = wp_normalize_path( untrailingslashit( $path ) );
		$themes_dir = wp_normalize_path( untrailingslashit( dirname( get_stylesheet_directory() ) ) );

		// Default URL.
		$url = plugins_url( '', $path . '/' . basename( $path ) . '.php' );

		// Included into themes.
		if (
			0 !== strpos( $path, wp_normalize_path( WP_PLUGIN_DIR ) )
			&& 0 !== strpos( $path, wp_normalize_path( WPMU_PLUGIN_DIR ) )
			&& 0 === strpos( $path, $themes_dir )
		) {
			$themes_url = untrailingslashit( dirname( get_stylesheet_directory_uri() ) );
			$url        = str_replace( $themes_dir, $themes_url, $path );
		}

		$path = trailingslashit( $path );
		$url  = trailingslashit( $url );

		return array( $path, $url );
	}

	/**
	 * Bootstrap the plugin.
	 */
	public function init() {
		$this->constants();

		// Register autoload for classes.
		require_once RWMB_INC_DIR . 'autoloader.php';
		$autoloader = new RWMB_Autoloader();
		$autoloader->add( RWMB_INC_DIR, 'RW_' );
		$autoloader->add( RWMB_INC_DIR, 'RWMB_' );
		$autoloader->add( RWMB_INC_DIR . 'about', 'RWMB_' );
		$autoloader->add( RWMB_INC_DIR . 'fields', 'RWMB_', '_Field' );
		$autoloader->add( RWMB_INC_DIR . 'walkers', 'RWMB_Walker_' );
		$autoloader->add( RWMB_INC_DIR . 'interfaces', 'RWMB_', '_Interface' );
		$autoloader->add( RWMB_INC_DIR . 'storages', 'RWMB_', '_Storage' );
		$autoloader->add( RWMB_INC_DIR . 'helpers', 'RWMB_Helpers_' );
		$autoloader->add( RWMB_INC_DIR . 'update', 'RWMB_Update_' );
		$autoloader->register();

		// Plugin core.
		$core = new RWMB_Core();
		$core->init();

		// Validation module.
		new RWMB_Validation();

		$sanitizer = new RWMB_Sanitizer();
		$sanitizer->init();

		$media_modal = new RWMB_Media_Modal();
		$media_modal->init();

		// WPML Compatibility.
		$wpml = new RWMB_WPML();
		$wpml->init();

		// Update.
		$update_option  = new RWMB_Update_Option();
		$update_checker = new RWMB_Update_Checker( $update_option );
		$update_checker->init();
		$update_settings = new RWMB_Update_Settings( $update_checker, $update_option );
		$update_settings->init();
		$update_notification = new RWMB_Update_Notification( $update_checker, $update_option );
		$update_notification->init();

		if ( is_admin() ) {
			$about = new RWMB_About( $update_checker );
			$about->init();

			new RWMB_Dashboard( 'http://feeds.feedburner.com/metaboxio', 'https://metabox.io/blog/', array(
				'title'           => 'Meta Box',
				'dismiss_tooltip' => esc_html__( 'Dismiss all Meta Box news', 'meta-box' ),
				'dismiss_confirm' => esc_html__( 'Are you sure to dismiss all Meta Box news?', 'meta-box' ),
			) );
		}

		// Public functions.
		require_once RWMB_INC_DIR . 'functions.php';
	}
}
