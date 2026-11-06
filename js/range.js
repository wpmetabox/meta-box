( function ( $, rwmb ) {
	'use strict';

	/**
	 * Update text value.
	 */
	function update() {
		const $this = $( this ),
			$output = $this.siblings( '.rwmb-range-output' );

		$output.text( $this.val() );
		$this.on( 'input propertychange change', () => $output.text( $this.val() ) );
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-range' ).each( update );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.rwmb-range', update );
} )( jQuery, rwmb );
