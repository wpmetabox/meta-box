<?php
/**
 * Load plugin's files with check for installing it as a standalone plugin or
 * a module of a theme / plugin. If standalone plugin is already installed, it
 * will take higher priority.
 * @package Meta Box
 */

/**
 * Plugin loader class
 * @package Meta Box
 */
class RWMB_Loader
{
	/**
	 * Plugin base URL
	 * @var string
	 */
	public $url;

	/**
	 * Plugin base path
	 * @var string
	 */
	public $dir;

	/**
	 * Class constructor
	 * @param string $url
	 * @param string $dir
	 */
	public function __construct( $url = '', $dir = '' )
	{
		$this->dir = $dir ? $dir : plugin_dir_path( dirname( __FILE__ ) );
		$this->dir = trailingslashit( wp_normalize_path( $this->dir ) );
		$this->url = $url ? esc_url_raw( $url ) : $this->base_url();

		$this->constants();
		$this->load_files();
	}

	/**
	 * Get plugin base URL
	 * @link http://www.deluxeblogtips.com/2013/07/get-url-of-php-file-in-wordpress.html
	 * @return string
	 */
	public function base_url()
	{
		// Get correct URL and path to wp-content
		$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
		$content_dir = untrailingslashit( dirname( dirname( get_stylesheet_directory() ) ) );
		$content_dir = wp_normalize_path( $content_dir );

		return str_replace( $content_dir, $content_url, $this->dir );
	}

	/**
	 * Define plugin constants
	 */
	public function constants()
	{
		// Script version, used to add version for scripts and styles
		define( 'RWMB_VER', '4.7.3' );

		// Plugin URLs, for fast enqueuing scripts and styles
		define( 'RWMB_URL', $this->url );
		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		// Plugin paths, for including files
		define( 'RWMB_DIR', $this->dir );
		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
		define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );
	}

	/**
	 * Load plugin's files
	 */
	public function load_files()
	{
		// Fields
		require_once RWMB_INC_DIR . 'field.php';
		foreach ( glob( RWMB_FIELDS_DIR . '*.php' ) as $file )
		{
			require_once $file;
		}

		// Meta Box class
		require_once RWMB_INC_DIR . 'meta-box.php';

		// Validation module
		require_once RWMB_INC_DIR . 'validation.php';
		new RWMB_Validation;

		// Helper function to retrieve meta value
		require_once RWMB_INC_DIR . 'helpers.php';

		// Main file
		require_once RWMB_INC_DIR . 'core.php';
		new RWMB_Core;
	}
}
