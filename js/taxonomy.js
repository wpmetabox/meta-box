( function ( $, rwmb ) {
	'use strict';

	function toggleAddInput( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'rwmb-hidden' );
	}

	rwmb.$document.on( 'click', '.rwmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
