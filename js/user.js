( function ( $, rwmb ) {
    'use strict';

    $( '.rwmb-user-add-button' ).rwmbModal( {
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

            //Input List ( checkbox or Radio )
            if ( $input.find( '.rwmb-input-list' ).length > 0 ) {
                $input.find( '.rwmb-input-list' ).attr( 'data-selected', this.$userId );
            }
        }
    } );

} )( jQuery, rwmb );
