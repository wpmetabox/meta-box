/**
 * Link: https://stackoverflow.com/questions/37386293/how-to-add-icon-in-select2
 */

( function ( $, rwmb ) {
	'use strict';

	function initIconField( event, options ) {
		$( this ).select2( {
			...options,
			templateResult: option => !option.id ? option.text : $( `<span class="rwmb-icon-select"><i class="${option.id}"></i>${option.text}</span>` ),
			templateSelection: option => !option.id ? option.text : $( `<span class="rwmb-icon-selected"><i class="${option.id}"></i>${option.text}</span>` ),
		} );
	}

	rwmb.$document
		.on( 'init_icon_field', '.rwmb-icon', initIconField );
} )( jQuery, rwmb );