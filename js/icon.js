/**
 * Link: https://stackoverflow.com/questions/37386293/how-to-add-icon-in-select2
 */

( function ( $, rwmb ) {
	'use strict';

	const label = option => option.text.replace( /<svg.*?>.*?<\/svg>/s, '' ).trim();

	const template = option => {
		if ( option.text.includes( '<svg' ) ) {
			const $span = $( '<span>', { class: 'rwmb-icon-select', title: label( option ) } );
			$span.html( option.text );
			return $span;
		}

		return option.id ? $( `<span class="rwmb-icon-select" title=${ option.text }><i class="${ option.id }"></i>${ option.text }</span>` ) : option.text;
	};

	function initIconField( event, options ) {
		$( this ).select2( {
			...options,
			templateResult: template,
			// selectWoo (loaded by WooCommerce) does not support jQuery objects and shows "[object Object]".
			templateSelection: $.fn.selectWoo ? label : template,
		} );
	}

	rwmb.$document
		.on( 'init_icon_field', '.rwmb-icon', initIconField );
} )( jQuery, rwmb );