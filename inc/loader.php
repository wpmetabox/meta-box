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

		/**
		 * Register autoloader for plugin classes.
		 * In PHP 5.3, SPL extension cannot be disabled and it's safe to use autoload.
		 * However, hosting providers can disable it in PHP 5.2. In that case, we provide a fallback for autoload.
		 * @link http://php.net/manual/en/spl.installation.php
		 * @link https://github.com/rilwis/meta-box/issues/810
		 */
		spl_autoload_register( array( $this, 'autoload' ) );
		if ( ! class_exists( 'RWMB_Core' ) )
		{
			$this->autoload_fallback();
		}

		$this->init();
	}

	/**
	 * Define plugin constants.
	 */
	public function constants()
	{
		// Script version, used to add version for scripts and styles
		define( 'RWMB_VER', '4.8.7' );

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
	public static function get_path( $base = '' )
	{
		// Plugin base path
		$path        = $base ? $base : dirname( dirname( __FILE__ ) );
		$path        = wp_normalize_path( untrailingslashit( $path ) );
		$content_dir = wp_normalize_path( untrailingslashit( WP_CONTENT_DIR ) );

		// Default URL
		$url = plugins_url( '', $path . '/' . basename( $path ) . '.php' );

		// Included into themes
		if (
			0 !== strpos( $path, wp_normalize_path( WP_PLUGIN_DIR ) )
			&& 0 !== strpos( $path, wp_normalize_path( WPMU_PLUGIN_DIR ) )
			&& 0 === strpos( $path, $content_dir )
		)
		{
			$content_url = untrailingslashit( dirname( dirname( get_stylesheet_directory_uri() ) ) );
			$url         = str_replace( $content_dir, $content_url, $path );
		}

		$path = trailingslashit( $path );
		$url  = trailingslashit( $url );

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
	 * Fallback for autoload in PHP 5.2.
	 */
	public function autoload_fallback()
	{
		$files = array(
			// Core
			'core',
			'helper',
			'meta-box',
			'validation',

			// Walkers
			'walkers/walker',
			'walkers/select-walker',
			'walkers/select-tree-walker',
			'walkers/input-list-walker',

			// Fields
			'field',

			'fields/multiple-values',
			'fields/autocomplete',
			'fields/text-list',

			'fields/choice',

			'fields/select',
			'fields/select-advanced',
			'fields/select-tree',

			'fields/input-list',
			'fields/radio',
			'fields/checkbox-list',

			'fields/object-choice',
			'fields/post',
			'fields/taxonomy',
			'fields/taxonomy-advanced',
			'fields/user',

			'fields/input',

			'fields/checkbox',
			'fields/number',
			'fields/range',

			'fields/text',
			'fields/color',
			'fields/datetime',
			'fields/date',
			'fields/time',
			'fields/email',
			'fields/fieldset-text',
			'fields/key-value',
			'fields/url',
			'fields/oembed',
			'fields/password',

			'fields/media',
			'fields/file-upload',
			'fields/image-advanced',
			'fields/image-upload',

			'fields/file-input',
			'fields/file',
			'fields/image',
			'fields/image-select',
			'fields/thickbox-image',

			'fields/button',
			'fields/custom-html',
			'fields/divider',
			'fields/heading',
			'fields/map',
			'fields/slider',
			'fields/textarea',
			'fields/wysiwyg',
		);
		foreach ( $files as $file )
		{
			require RWMB_INC_DIR . "$file.php";
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

		// Public functions
		require RWMB_INC_DIR . 'functions.php';
	}
}
