( function ( $, rwmb ) {
    'use strict';

    function addNew() {
        const $this = $( this );

        $this.rwmbModal( {
            removeElement: '.form-wrap > h2',
            callback: function ( $modal, $modalContent ) {
                $modalContent.find( '#col-right' ).css( 'display', 'none' );
                $modalContent.find( '.search-box' ).css( 'display', 'none' );
                $modalContent.find( '#wpbody' ).css( 'padding-top', 0 );
                $modal.css( {
                    'max-width': '480px',
                    'margin': 'auto',
                } );
            },
            closeModalCallback: function ( $modal, $input ) {
                if ( $modal.find( '#the-list tr:first td:eq(0) .row-actions' ).length > 0 ) {
                    this.$objectId = parseInt( $modal.find( '#the-list tr:first' ).attr( 'id' ).split( '-' )[ 1 ] );
                    this.$objectDisplay = $modal.find( '#the-list tr:first td:eq(0) strong a' ).text();
                }
            }
        } );
    }

    function init( e ) {
        const wrapper = e.target || e;
        $( wrapper ).find( '.rwmb-taxonomy-add-button' ).each( addNew );
    }

    rwmb.$document
        .on( 'mb_ready', init )
        .on( 'clone', function ( e ) {
            init( $( e.target ).parent() );
        } );

} )( jQuery, rwmb );
