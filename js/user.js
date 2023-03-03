( function ( $, rwmb ) {
    'use strict';

    $( '.rwmb-user-add-button' ).rwmbModal( {
        removeElement: '#add-new-user',
        callback: function ( modal ) {
            $( modal ).find( '#add-new-user' ).next().next().remove();
            $( modal ).find( '#wpcontent' ).css( 'margin-left', 0 );
        }
    } );
    
} )( jQuery, rwmb );
