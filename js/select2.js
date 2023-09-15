( function ( $, rwmb ) {
	'use strict';

	function select2Open( e ) {
		if ( $( "#wpadminbar" ).length === 0 ) {
			return;
		}

		if ( $( e.target ).next().hasClass( 'select2-container--above' ) ) {
			var regex = /\/(wp-admin|admin)\//;
			if ( regex.test( window.location.href ) ) {
				$( 'body > .select2-container--open .select2-dropdown--above' ).css( 'top', 0 );
				return;
			}

			$( 'body > .select2-container:last-child > .select2-dropdown' ).css( 'top', $( document.body ).offset().top );
		}
	};

	rwmb.$document
		.on( 'select2:open', select2Open );
} )( jQuery, rwmb );