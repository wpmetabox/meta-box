( function( $, document, i18n ) {
	'use strict';

	function dismissNotification() {
		$( '#meta-box-notification' ).on( 'click', '.notice-dismiss', function( event ) {
			event.preventDefault();

			$.post( ajaxurl, {
				action: 'mb_dismiss_notification',
				nonce: MBNotification.nonce
			} );
		} );
	}

	$( document ).on( 'ready', dismissNotification );
} )( jQuery, document, MBNotification );
