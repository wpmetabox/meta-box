<?php

class RWMB_Loader
{
	static function load( $url, $dir )
	{
		define( 'RWMB_VER', '4.7.3' );

		define( 'RWMB_URL', $url );
		define( 'RWMB_DIR', $dir );

		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc' ) );
		define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );
	}
}
