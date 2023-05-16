( function ( $, rwmb ) {
    'use strict';

    function addNew() {
        const $this = $( this );

        $this.rwmbModal( {
            $taxId: null,
            $taxName: null,
            removeElement: '.form-wrap > h2',
            closeModalCallback: function ( $modal, $input ) {
                if ( $modal.find( '#the-list tr:first td:eq(0) .row-actions' ).length > 0 ) {
                    this.$taxId = parseInt( $modal.find( '#the-list tr:first' ).attr( 'id' ).split( '-' )[ 1 ] );
                    this.$taxName = $modal.find( '#the-list tr:first td:eq(0) strong a' ).text();
                }

                if ( !this.$taxId ) {
                    return;
                }

                if ( $input.find( '> *[data-options]' ).length > 1 || $input.find( '.rwmb-select-tree, .rwmb-select' ).length > 0 ) {
                    $input.find( 'select' ).attr( 'data-selected', this.$taxId );
                    $input.find( 'select :selected' ).removeAttr( 'selected' );

                    if ( $input.find( '.rwmb-select' ).length > 0 ) {
                        return;
                    }

                    $input.find( 'select' ).prepend( $( '<option>', {
                        value: this.$taxId,
                        text: this.$taxName,
                        selected: true
                    } ) );

                    return;
                }

                //Input List ( checkbox or Radio )
                if ( $input.find( '.rwmb-input-list' ).length > 0 ) {
                    $input.find( '.rwmb-input-list' ).attr( 'data-selected', this.$taxId );
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
