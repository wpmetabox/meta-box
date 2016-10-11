jQuery( function ( $ ) {
	'use strict';

	$( 'body' ).on( 'change', '.rwmb-image-select input', function () {
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
	} );
	$( '.rwmb-image-select input' ).trigger( 'change' );
} );
