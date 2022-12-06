<?php
/**
 * Autoload plugin classes.
 */
class RWMB_Autoloader {
	protected $dirs = [];

	/**
	 * Adds a base directory for a class name prefix and/or suffix.
	 *
	 * @param string $dir    A base directory for class files.
	 * @param string $prefix The class name prefix.
	 * @param string $suffix The class name suffix.
	 */
	public function add( string $dir, string $prefix, string $suffix = '' ) {
		$this->dirs[] = [
			'dir'    => trailingslashit( $dir ),
			'prefix' => $prefix,
			'suffix' => $suffix,
		];
	}

	public function register() {
		spl_autoload_register( [ $this, 'autoload' ] );
	}

	public function autoload( string $class ) {
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
			if ( function_exists( 'mb_strtolower' ) && function_exists( 'mb_detect_encoding' ) ) {
				$file = mb_strtolower( str_replace( '_', '-', $file ), mb_detect_encoding( $file ) ) . '.php';
			} else {
				$file = strtolower( str_replace( '_', '-', $file ) ) . '.php';
			}
			$file = $dir['dir'] . $file;
			$this->require( $file );
		}
	}

	private function require( string $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
