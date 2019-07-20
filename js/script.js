// Global object for shared functions and data.
window.rwmb = window.rwmb || {};

( function( $, document, rwmb ) {
	'use strict';

	// Trigger a custom ready event for all scripts to hook to.
	// Used for static DOM and dynamic DOM (loaded in MB Blocks extension for Gutenberg).
	rwmb.$document = $( document );
	rwmb.$document.on( 'ready', function() {
		rwmb.$document.trigger( 'mb_ready' );
	} );
} )( jQuery, document, rwmb );