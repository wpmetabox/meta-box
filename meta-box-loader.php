<?php

class RWMB_Loader
{
 	static function load( $RWMB_URL, $RWMB_DIR )
 	{
		define( 'RWMB_URL', $RWMB_URL );
		define( 'RWMB_DIR', $RWMB_DIR );

		define( 'RWMB_JS_URL', trailingslashit( RWMB_URL . 'js' ) );
		define( 'RWMB_CSS_URL', trailingslashit( RWMB_URL . 'css' ) );

		define( 'RWMB_INC_DIR', trailingslashit( RWMB_DIR . 'inc') );
		define( 'RWMB_FIELDS_DIR', trailingslashit( RWMB_INC_DIR . 'fields' ) );
    	}
}
