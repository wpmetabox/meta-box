( function ( $, rwmb ) {
    'use strict';

    $( '.rwmb-taxonomy-add-button' ).rwmbModal( {
        removeElement: '.form-wrap > h2',
        callback: function ( modal ) {
            $( modal ).find( '#wpcontent' ).css( 'margin-left', 0 );
        }
    } );
    
} )( jQuery, rwmb );
