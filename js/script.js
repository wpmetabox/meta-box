( function( $, document, window ) {
	'use strict';

	// Global object for shared functions and data.
	window.rwmb = {};

	// Trigger a custom ready event for all scripts to hook to.
	// Used for static DOM and dynamic DOM (loaded in MB Blocks extension for Gutenberg).
	var $document = $( document );
	$document.on( 'ready', function() {
		$document.trigger( 'mb_ready' );
	} );
} )( jQuery, document, window );