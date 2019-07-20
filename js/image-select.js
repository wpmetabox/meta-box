( function ( $, rwmb ) {
	'use strict';

	function setActiveClass() {
		var $this = $( this ),
			type = $this.attr( 'type' ),
			selected = $this.is( ':checked' ),
			$parent = $this.parent(),
			$others = $parent.siblings();
		if ( selected ) {
			$parent.addClass( 'rwmb-active' );
			if ( type === 'radio' ) {
				$others.removeClass( 'rwmb-active' );
			}
		} else {
			$parent.removeClass( 'rwmb-active' );
		}
	}

	function init( e ) {
		$( e.target ).find( '.rwmb-image-select input' ).trigger( 'change' );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.rwmb-image-select input', setActiveClass );
} )( jQuery, rwmb );
