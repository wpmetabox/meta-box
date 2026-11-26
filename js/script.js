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

	// Hidden inputs keep their current value on the native form reset, so save their initial value to restore it.
	const hiddenInputs = 'input[type="hidden"][class*="rwmb"]';
	rwmb.$document.on( 'mb_ready', e => {
		$( e.target ).find( hiddenInputs ).each( function () {
			$( this ).data( 'initial-value', this.value );
		} );
	} );

	// Trigger 'mb_reset' on a form after its values are reset, so fields can update their UI.
	// The native 'reset' event fires before the values are reset, hence the timeout.
	rwmb.$document.on( 'reset', e => setTimeout( () => {
		$( e.target ).find( hiddenInputs ).each( function () {
			const value = $( this ).data( 'initial-value' );
			if ( undefined !== value ) {
				this.value = value;
			}
		} );
		$( e.target ).trigger( 'mb_reset' );
	} ) );
} )( jQuery, document, rwmb );