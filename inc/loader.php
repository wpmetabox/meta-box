<?php
/**
 * Load plugin's files with check for installing it as a standalone plugin or
 * a module of a theme / plugin. If standalone plugin is already installed, it
 * will take higher priority.
 * @package Meta Box
 */

/**
 * Plugin loader class.
 * @package Meta Box
 */
class RWMB_Loader
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		$this->constants();
		spl_autoload_register( array( $this, 'autoload' ) );
		$this->init();
	}

	/**
	 * Define plugin constants.
	 */
	public function constants()
	{
		// Script version, used to add version for scripts and styles
		define( 'RWMB_VER', '4.8.2' );

		list( $path, $url ) = self::get_path();

		// Plugin URLs, for fast enqueuing scripts and styles
		define( 'RWMB_URL', $url );
		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		// Plugin paths, for including files
		define( 'RWMB_DIR', $path );
		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
		define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );
	}

	/**
	 * Get plugin base path and URL.
	 * The method is static and can be used in extensions.
	 * @link http://www.deluxeblogtips.com/2013/07/get-url-of-php-file-in-wordpress.html
	 * @param string $base Base folder path
	 * @return array Path and URL.
	 */
	static public function get_path( $base = '' )
	{
		// Plugin base path
		$path = $base ? $base : dirname( dirname( __FILE__ ) );

		// Check if plugin is a symbolic link (only when it's installed as a standalone plugin).
		if ( false === strpos( $path, ABSPATH ) )
		{
			if ( ! function_exists( 'is_plugin_active' ) )
			{
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$basename = basename( $path );
			if ( is_plugin_active( "$basename/$basename.php" ) )
			{
				$path = trailingslashit( WP_PLUGIN_DIR ) . $basename;
			}
		}

		$path = trailingslashit( wp_normalize_path( $path ) );

		// Get plugin base URL
		$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
		$content_dir = untrailingslashit( WP_CONTENT_DIR );
		$content_dir = wp_normalize_path( $content_dir );
		$url         = str_replace( $content_dir, $content_url, $path );

		return array( $path, $url );
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
		$file = 'meta-box';
		if ( 'RW_Meta_Box' != $class )
		{
			// Remove prefix 'RWMB_'
			$file = substr( $class, 5 );

			// Optional '_Field'
			$file = preg_replace( '/_Field$/', '', $file );
		}

		$file = strtolower( str_replace( '_', '-', $file ) ) . '.php';

		$dirs = array( RWMB_INC_DIR, RWMB_FIELDS_DIR, trailingslashit( RWMB_INC_DIR . 'walkers' ) );
		foreach ( $dirs as $dir )
		{
			if ( file_exists( $dir . $file ) )
			{
				require $dir . $file;
				return;
			}
		}
	}

	/**
	 * Initialize plugin.
	 */
	public function init()
	{
		// Plugin core
		new RWMB_Core;

		if ( is_admin() )
		{
			// Validation module
			new RWMB_Validation;
		}
		else
		{
			// Public functions
			require RWMB_INC_DIR . 'functions.php';
		}
	}
}
