( function ( $, rwmb ) {
	'use strict';

	$( '.rwmb-post-add-button' ).rwmbModal( {
		removeElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
		callback: function( $modal ) {
			const isBlockEditor = $modal.find( 'body' ).hasClass( 'block-editor-page' );

			if ( !isBlockEditor ) {
				return;
			}

			setTimeout( () => {
				const $ui = $modal.find( '.interface-interface-skeleton' );
				$ui.css( {
					left: 0,
					top: 0
				} );
				$ui.find( '.interface-interface-skeleton__editor' ).css( 'overflow', 'scroll' );
			}, 500 );
		}
	} );

} )( jQuery, rwmb );
