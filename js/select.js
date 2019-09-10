// Global object for shared functions and data.
window.rwmb = window.rwmb || {}; // add this for remove rwmb is not defined

( function ( $ , document, rwmb ) { // include document and rwmb
	'use strict';
	rwmb.$document = $( document ); // include document inside rwmb
	rwmb.$document.on( 'ready', function() {
		rwmb.$document.trigger( 'mb_ready' ); // check ready to continue rwmb
	} );
	function toggleAll( e ) {
		e.preventDefault();

		var $this = $( this ),
			$select = $this.parent().siblings( 'select' );

		if ( 'none' === $this.data( 'type' ) ) {
			$select.val( [] ).trigger( 'change' );
			return;
		}
		var selected = [];
		$select.find( 'option' ).each( function ( index, option ) {
			selected.push( option.value );
		} );
		$select.val( selected ).trigger( 'change' );
	};

	rwmb.$document.on( 'click', '.rwmb-select-all-none a', toggleAll );
} )( jQuery, document, rwmb );  // include document and rwmb

