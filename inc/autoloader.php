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
	 * @param string $dir    A base directory for class files.
	 * @param string $prefix The class name prefix.
	 * @param string $suffix The class name suffix.
	 */
	public function add( $dir, $prefix, $suffix = '' ) {
		$this->dirs[] = array(
			'dir'    => trailingslashit( $dir ),
			'prefix' => $prefix,
			'suffix' => $suffix,
		);
	}

	/**
	 * Register autoloader for plugin classes.
	 */
	public function register() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		foreach ( $this->dirs as $dir ) {
			if (
				( $dir['prefix'] && 0 !== strpos( $class, $dir['prefix'] ) )
				|| ( $dir['suffix'] && substr( $class, - strlen( $dir['suffix'] ) ) !== $dir['suffix'] )
			) {
				continue;
			}
			$file = substr( $class, strlen( $dir['prefix'] ) );
			if ( $dir['suffix'] && strlen( $file ) > strlen( $dir['suffix'] ) ) {
				$file = substr( $file, 0, - strlen( $dir['suffix'] ) );
			}
			$file = strtolower( str_replace( '_', '-', $file ) ) . '.php';
			$file = $dir['dir'] . $file;
			$this->require_file( $file );
		}
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The file to require.
	 */
	protected function require_file( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
