( function ( $, rwmb ) {
    'use strict';

    function setIcon() {
        var $this = $( this ),
            $icon = $( this ).siblings( 'i' );
        if ( $icon.length == 0 ) {
            $icon = $( '<i></i><br>' );
            $icon.attr( 'class', $this.val() );
            $this.before( $icon );
        } else {
            $icon.attr( 'class', $this.val() );
        }
    }

    function init( e ) {
        $( e.target ).find( '.rwmb-icon' ).each( transform );
        $( e.target ).find( '.rwmb-icon' ).each( setIcon );
    }

    /**
     * Transform select fields into beautiful dropdown with select2 library.
     */
    function transform() {
        var $this = $( this ),
            options = $this.data( 'options' );
        console.log( $this );

        $this.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' );
        $this.siblings( '.select2-container' ).remove();
        $this.find( 'option' ).removeAttr( 'data-select2-id' );

        $this.show().select2( options );

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
        .on( 'change', '.rwmb-icon', setIcon )
        .on( 'clone', function ( e ) {
            init( $( e.target ).parent() );
        } );
} )( jQuery, rwmb );