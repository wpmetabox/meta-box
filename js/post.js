( function ( $, rwmb ) {
    'use strict';

    $( '.rwmb-post-add-button' ).rwmbModal( {
        removeElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
        callback: function ( modal ) {
            if ( !$( modal ).find( 'html' ).hasClass( 'interface-interface-skeleton__html-container' ) ) {
                $( modal ).find( '#wpcontent' ).css( 'margin-left', 0 );
            }
            $( modal ).find( '#editor .interface-interface-skeleton' ).css( 'position', 'relative' );
            $( modal ).find( '#editor .interface-interface-skeleton__editor' ).css( 'overflow', 'scroll' );
        }
    } );

} )( jQuery, rwmb );
