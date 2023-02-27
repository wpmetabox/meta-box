( function ( $, rwmb ) {
    'use strict';

    const defaultOptions = {
        warapper: '<div class="rwmb-modal"><div class="rwmb-modal-title"><span>HEADER</span><i class="rwmb-modal-close"></i></div><div class="rwmb-modal-content"></div></div>',
        markupIframe: '<iframe id="rwmb-modal-iframe" width="100%" height="700" src="{0}" border="0"></iframe>',
        markupOverlay: '<div class="rwmb-modal-overlay"></div>'
    };

    $.fn.rwmbModal = function ( options = {} ) {
        options = {
            ...defaultOptions,
            ...options
        };        

        if ( $( '.rwmb-modal' ).length === 0 ) {
            return;
        }

        const rwmb_input = $( this ).closest( '.rwmb-input' );        

        $( this ).on( 'click', function ( e ) {            
            $( '.rwmb-modal .rwmb-modal-title span' ).html( $( this ).html() );
            $( '.rwmb-modal .rwmb-modal-content' ).html( options.markupIframe.format( $( this ).data( 'url' ) ) );
            $( '#rwmb-modal-iframe' ).on( 'load', function () {
                $( this ).contents().find( '#adminmenumain, #wpadminbar, #wpfooter, .row-actions, .form-wrap.edit-term-notes, #screen-meta-links' ).remove();
                $( this ).contents().find( 'a' ).on( 'click', function ( e ) {
                    e.preventDefault();
                    return false;
                } );
                const head = $( this ).contents().find( 'head' );
                const css = '<style>#wpcontent{margin-left: 0;}</style>';
                $( head ).append( css );
                $( 'body' ).addClass( 'rwmb-modal-show' );
                $( '.rwmb-modal-overlay' ).fadeIn( 'medium' );
                $( '.rwmb-modal' ).fadeIn( 'medium' );
            } );

            $( '.rwmb-modal-close' ).on( 'click', function () {
                $( '.rwmb-modal' ).fadeOut( 'medium' );
                $( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
                $( 'body' ).removeClass( 'rwmb-modal-show' );
                rwmb_input.find( '> *[data-options]' ).rwmbTransform();
            })
        })
    };

    ( () => {
        if ( $( '.rwmb-modal' ).length === 0 ) {
            $( 'body' ).append( defaultOptions.warapper );
            $( 'body' ).append( defaultOptions.markupOverlay );
        }
    } )();

    if ( !String.prototype.format ) {
        String.prototype.format = function () {
            var args = arguments;
            return this.replace( /{(\d+)}/g, function ( match, number ) {
                return typeof args[ number ] != 'undefined'
                    ? args[ number ]
                    : match
                    ;
            } );
        };
    }    
} )( jQuery, rwmb );