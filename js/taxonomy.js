jQuery( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.rwmb-taxonomy-add-button', function( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'rwmb-hidden' );
	} );
} );
