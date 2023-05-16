( function ( $, rwmb ) {
    'use strict';

    function addNew() {
        const $this = $( this );

        $this.rwmbModal( {
            $userId: null,
            $displayName: null,
            removeElement: '#add-new-user',
            callback: function ( $modal ) {
                $modal.find( '#add-new-user' ).next().next().remove();
            },
            closeModalCallback: function ( $modal, $input ) {
                if ( $modal.find( '#wpbody-content .wrap form input[name="_wp_http_referer"]' ).length > 0 ) {
                    const urlParams = new URLSearchParams( $modal.find( '#wpbody-content .wrap form input[name="_wp_http_referer"]' ).val() );
                    this.$userId = parseInt( urlParams.get( 'id' ) );
                    this.$displayName = $modal.find( `#the-list tr[id="user-${ this.$userId }"] .column-name` ).text();
                }

                if ( !this.$userId ) {
                    return;
                }

				// Select advanced, select tree, select.
                if ( $input.find( '> *[data-options]' ).length > 1 || $input.find( '.rwmb-select-tree, .rwmb-select' ).length > 0 ) {
                    $input.find( 'select' ).attr( 'data-selected', this.$userId );
                    $input.find( 'select :selected' ).removeAttr( 'selected' );

                    if ( $input.find( '.rwmb-select' ).length > 0 ) {
                        return;
                    }

                    $input.find( 'select' ).prepend( $( '<option>', {
                        value: this.$userId,
                        text: this.$displayName,
                        selected: true
                    } ) );

                    return;
                }

				// Input list (checkbox list or radio).
                if ( $input.find( '.rwmb-input-list' ).length > 0 ) {
                    $input.find( '.rwmb-input-list' ).attr( 'data-selected', this.$userId );
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
