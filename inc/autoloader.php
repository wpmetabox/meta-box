<?php
/**
 * Autoload plugin classes.
 *
 * @package Meta Box
 */

/**
 * Autoload class
 */
class RWMB_Autoloader {

	/**
	 * List of directories to load classes.
	 *
	 * @var array
	 */
	protected $dirs = array();

	/**
	 * Adds a base directory for a class name prefix and/or suffix.
	 *
	 * @param string $base_dir A base directory for class files.
	 * @param string $prefix   The class name prefix.
	 * @param string $suffix   The class name suffix.
	 */
	public function add( $base_dir, $prefix, $suffix = '' ) {
		$this->dirs[] = array(
			'dir'    => trailingslashit( $base_dir ),
			'prefix' => $prefix,
			'suffix' => $suffix,
		);
	}

	/**
	 * Register autoloader for plugin classes.
	 * In PHP 5.3, SPL extension cannot be disabled and it's safe to use autoload.
	 * However, hosting providers can disable it in PHP 5.2. In that case, we provide a fallback for autoload.
	 *
	 * @link http://php.net/manual/en/spl.installation.php
	 * @link https://github.com/rilwis/meta-box/issues/810
	 */
	public function register() {
		spl_autoload_register( array( $this, 'autoload' ) );
		if ( ! class_exists( 'RWMB_Core' ) ) {
			$this->fallback();
		}
	}

	/**
	 * Autoload fields' classes.
	 *
	 * @param string $class Class name.
	 * @return mixed Boolean false if no mapped file can be loaded, or the name of the mapped file that was loaded.
	 */
	public function autoload( $class ) {
		foreach ( $this->dirs as $dir ) {
			if (
				( $dir['prefix'] && 0 !== strpos( $class, $dir['prefix'] ) )
				&& ( $dir['suffix'] && substr( $class, - strlen( $dir['suffix'] ) ) !== $dir['suffix'] )
			) {
				continue;
			}
			$file = substr( $class, strlen( $dir['prefix'] ) );
			if ( $dir['suffix'] && strlen( $file ) > strlen( $dir['suffix'] ) ) {
				$file = substr( $file, 0, - strlen( $dir['suffix'] ) );
			}
			$file = strtolower( str_replace( '_', '-', $file ) ) . '.php';
			$file = $dir['dir'] . $file;
			if ( $this->require_file( $file ) ) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Fallback for autoload in PHP 5.2.
	 */
	protected function fallback() {
		$files = array(
			// Core.
			'core',
			'clone',
			'helper',
			'meta-box',
			'validation',
			'sanitize',

			// Walkers.
			'walkers/walker',
			'walkers/select',
			'walkers/select-tree',
			'walkers/input-list',

			// Fields.
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
			'fields/fieldset-text',
			'fields/key-value',
			'fields/oembed',
			'fields/password',

			'fields/file-input',
			'fields/file',
			'fields/image',
			'fields/image-select',
			'fields/thickbox-image',

			'fields/media',
			'fields/file-upload',
			'fields/image-advanced',
			'fields/image-upload',

			'fields/button',
			'fields/custom-html',
			'fields/divider',
			'fields/heading',
			'fields/map',
			'fields/slider',
			'fields/textarea',
			'fields/wysiwyg',
		);
		foreach ( $files as $file ) {
			$this->require_file( RWMB_INC_DIR . "$file.php" );
		}
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The file to require.
	 * @return bool True if the file exists, false if not.
	 */
	protected function require_file( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
			return true;
		}
		return false;
	}
}
