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

	function reset( e ) {
		$( e.target ).find( '.rwmb-range' ).each( function () {
			$( this ).siblings( '.rwmb-range-output' ).text( this.value );
		} );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'mb_reset', reset )
		.on( 'clone', '.rwmb-range', update );
} )( jQuery, rwmb );
