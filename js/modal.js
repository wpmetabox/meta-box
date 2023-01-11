( function ( $, rwmb ) {
    'use strict';

    const defaultOptions = {
        warapper: '<div class="rwmb-modal"><div class="rwmb-modal-title"><span>HEADER</span><i class="rwmb-modal-close"></i></div><div class="rwmb-modal-content"></div></div>',
        markupIframe: '<iframe id="rwmb-modal-iframe" width="100%" height="700" src="{0}" border="0"></iframe>',
        markupOverlay: '<div class="rwmb-modal-overlay"></div>'
    };

    /**
     * Transform select fields into beautiful dropdown with select2 library.
     */
    function transform() {
        var $this = $( this ),
            options = $this.data( 'options' );

        $this.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' );
        $this.siblings( '.select2-container' ).remove();
        $this.find( 'option' ).removeAttr( 'data-select2-id' );

        if ( options.ajax_data ) {
            options.ajax.dataType = 'json';
            options.ajax.data = function ( params ) {
                return Object.assign( options.ajax_data, params );
            };
            options.ajax.processResults = function ( response ) {
                var items = response.data.items.map( function ( item ) {
                    return {
                        id: item.value,
                        text: _.unescape( item.label ),
                    };
                } );

                var results = {
                    results: items
                };
                if ( response.data.hasOwnProperty( 'more' ) ) {
                    results.pagination = { more: true };
                }

                return results;
            };

            options.ajax.transport = function ( params, success, failure ) {
                if ( params.data._type === 'query' ) {
                    delete params.data.page;
                }

                // Create cache key from ajax params from only neccessary keys to make cache available for multiple fields.
                var data = $.extend( true, {}, params.data );
                delete data.field.id;
                delete data.action;
                if ( !data.term ) {
                    delete data.term;
                }

                var key = JSON.stringify( data );
                if ( cache[ key ] ) {
                    success( cache[ key ] );
                    return;
                }

                var actions = {
                    'post': 'rwmb_get_posts',
                    'taxonomy': 'rwmb_get_terms',
                    'taxonomy_advanced': 'rwmb_get_terms',
                    'user': 'rwmb_get_users'
                };
                params.data.action = actions[ params.data.field.type ];
                params.method = 'POST';

                return $.ajax( params ).then( function ( data ) {
                    cache[ key ] = data;
                    return data;
                } ).then( success ).fail( failure );
            };
        }

        $this.show().select2( options );

        if ( !$this.attr( 'multiple' ) ) {
            return;
        }

        reorderSelected( $this );

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

    $.fn.rwmbModal = function ( options = {} ) {
        options = {
            ...defaultOptions,
            ...options
        };        

        if ( $( '.rwmb-modal' ).length === 0 ) {
            return;
        }

        console.log( $( this ).closest( '.rwmb-input' ).find( 'select' ).data( 'options' ) );
        

        $( this ).on( 'click', function ( e ) {            
            $( '.rwmb-modal .rwmb-modal-title span' ).html( $( this ).html() );
            $( '.rwmb-modal .rwmb-modal-content' ).html( options.markupIframe.format( $( this ).data( 'url' ) ) );
            $( '#rwmb-modal-iframe' ).on( 'load', function () {
                let head = $( this ).contents().find( 'head' );
                let css = '<style>#adminmenumain, #wpcontent #wpadminbar, #wpfooter {display: none;} #wpcontent, #wpfooter{margin-left: 0;}</style>';
                $( head ).append( css );
                $( 'body' ).addClass( 'rwmb-modal-show' );
                $( '.rwmb-modal-overlay' ).fadeIn( 'medium' );
                $( '.rwmb-modal' ).fadeIn( 'medium' );
            } );

            $( '.rwmb-modal-close' ).on( 'click', function () {
                $( '.rwmb-modal' ).fadeOut( 'medium' );
                $( '.rwmb-modal-overlay' ).fadeOut( 'medium' );
                $( 'body' ).removeClass( 'rwmb-modal-show' );
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