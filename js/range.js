jQuery( function ( $ ) {
	'use strict';

	/**
	 * Update color picker element
	 * Used for static & dynamic added elements (when clone)
	 */
	function update() {
		var $this = $( this ),
			$output = $this.siblings( '.rwmb-output' );

		$this.on( 'input propertychange change', function ( e ) {
			$output.html( $this.val() );
		} );

	}

	$( '.rwmb-range' ).each( update );
	$( document ).on( 'clone', '.rwmb-range', update );
} );
