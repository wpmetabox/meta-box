/**
 * Link: https://stackoverflow.com/questions/37386293/how-to-add-icon-in-select2
 */

( function ( $, rwmb ) {
	'use strict';

	const template = option => option.id ? $( `<span class="rwmb-icon-select"><i class="${ option.id }"></i>${ option.text }</span>` ) : option.text;

	function initIconField( event, options ) {
		$( this ).select2( {
			...options,
			templateResult: template,
			templateSelection: template,
		} );
	}

	rwmb.$document
		.on( 'init_icon_field', '.rwmb-icon', initIconField );
} )( jQuery, rwmb );