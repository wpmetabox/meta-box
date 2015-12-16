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
		spl_autoload_register( array( $this, 'autoload' ) );
		$this->init();
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
	 * Autoload fields' classes.
	 * @param string $class Class name
	 */
	public function autoload( $class )
	{
		// Only load plugin's classes
		if ( 'RW_Meta_Box' != $class && 0 !== strpos( $class, 'RWMB_' ) )
		{
			return;
		}

		// Get file name
		if ( 'RW_Meta_Box' == $class )
		{
			$file = 'meta-box';
		}
		else
		{
			// Remove prefix 'RWMB_'
			$file = substr( $class, 5 );

			// Optional '_Field'
			$file = preg_replace( '/_Field$/', '', $file );
		}

		$file = strtolower( str_replace( '_', '-', $file ) ) . '.php';

		$dirs = array( RWMB_INC_DIR, RWMB_FIELDS_DIR );
		foreach ( $dirs as $dir )
		{
			if ( file_exists( trailingslashit( $dir ) . $file ) )
			{
				require trailingslashit( $dir ) . $file;
			}
		}
	}

	/**
	 * Initialize plugin
	 */
	public function init()
	{
		// Bootstrap
		new RWMB_Core;

		// Validation module
		new RWMB_Validation;

		// Helper class and functions to retrieve meta value
		require RWMB_INC_DIR . 'helpers.php';
	}
}
