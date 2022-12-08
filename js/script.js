// Global object for shared functions and data.
window.rwmb = window.rwmb || {};

( function( $, document, rwmb ) {
	'use strict';

	// Selectors for all plugin inputs.
	rwmb.inputSelectors = 'input[class*="rwmb"], textarea[class*="rwmb"], select[class*="rwmb"], button[class*="rwmb"]';

	// Detect Gutenberg.
	rwmb.isGutenberg = document.body.classList.contains( 'block-editor-page' );

	// Generate unique ID.
	rwmb.uniqid = () => Math.random().toString( 36 ).substr( 2 );

	// Trigger a custom ready event for all scripts to hook to.
	// Used for static DOM and dynamic DOM (loaded in MB Blocks extension for Gutenberg).
	rwmb.$document = $( document );

	$( function() {
		rwmb.$document.trigger( 'mb_ready' );
	} );
} )( jQuery, document, rwmb );