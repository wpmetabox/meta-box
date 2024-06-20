( function ( $, rwmb ) {
    'use strict';

    function addNew() {
        const $this = $( this );

        $this.rwmbModal( {
            size: 'small',
            hideElement: '#add-new-user',
            callback: function ( $modal, $modalContent ) {
                $modalContent.find( '#add-new-user' ).next().next().remove();
            },
            closeModalCallback: function ( $modal, $input ) {
                if ( $modal.find( '#wpbody-content .wrap form input[name="_wp_http_referer"]' ).length > 0 ) {
                    const urlParams = new URLSearchParams( $modal.find( '#wpbody-content .wrap form input[name="_wp_http_referer"]' ).val() );
                    this.$objectId = parseInt( urlParams.get( 'id' ) );
                    this.$objectDisplay = $modal.find( `#the-list tr[id="user-${ this.$objectId }"] .column-name` ).text() !== '—Unknown' ?
                        $modal.find( `#the-list tr[id="user-${ this.$objectId }"] .column-name` ).text() :
                        $modal.find( `#the-list tr[id="user-${ this.$objectId }"] .column-username strong a` ).text();
                }
            }
        } );
    }

    function init( e ) {
        const wrapper = e.target || e;
        $( wrapper ).find( '.rwmb-user-add-button' ).each( addNew );
    }

    rwmb.$document
        .on( 'mb_ready', init )
        .on( 'clone', function ( e ) {
            init( $( e.target ).parent() );
        } );

} )( jQuery, rwmb );
