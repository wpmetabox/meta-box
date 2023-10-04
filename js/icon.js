/**
 * Link: https://stackoverflow.com/questions/37386293/how-to-add-icon-in-select2
 */

( function ( $, rwmb ) {
    'use strict';

    function init( e ) {
        $( e.target ).find( '.rwmb-icon' ).each( transform );
    }

    /**
     * Transform select fields into beautiful dropdown with select2 library.
     */
    function transform() {
        var $this = $( this ),
            options = $this.data( 'options' );

        $this.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' );
        $this.siblings( '.select2-container' ).remove();
        $this.find( 'option' ).removeAttr( 'data-select2-id' );

        $this.show().select2( {
            ...options,
            templateResult: function ( option ) {
                if ( !option.id ) {
                    return option.text;
                }

                const $option = $( '<span class="rwmb-icon-select"><i class="' + option.id + '"></i> ' + option.text + '</span>' );
                return $option;
            },
            templateSelection: function ( option ) {
                if ( !option.id ) {
                    return option.text;
                }

                const $option = $( '<span class="rwmb-icon-selected"><i class="' + option.id + '"></i><span style="margin-left:5px">' + option.text + '</span></span>' );
                return $option;
            },
        } );

        /**
         * Preserve the order that options are selected.
         * @see https://github.com/select2/select2/issues/3106#issuecomment-255492815
         */
        $this.on( 'select2:select', function ( event ) {
            var option = $this.children( '[value="' + event.params.data.id + '"]' );
            option.detach();
            $this.append( option ).trigger( 'change' );
        } );
    }

    rwmb.$document
        .on( 'mb_ready', init )
        .on( 'clone', function ( e ) {
            init( $( e.target ).parent() );
        } );
} )( jQuery, rwmb );