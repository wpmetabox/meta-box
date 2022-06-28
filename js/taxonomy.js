( function ( $, rwmb ) {
	'use strict';

	function toggleAddInput( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'rwmb-hidden' );
	}

    function focusOutInput() {
        const required = $( this ).val( ) == '';
        $( this ).closest( '.rwmb-input' ).find( rwmb.inputSelectors ).rules( 'add', {
            required
        } );
        $( this ).closest( '.rwmb-input' ).find( rwmb.inputSelectors ).removeClass( 'rwmb-error' );
    }
    
    function refreshAfterSave( ) {
        var object = $( this );

        if( object.find( 'input' ).val( ) === '' ) {
            object.find( '.rwmb-taxonomy-add-form' ).addClass( 'rwmb-hidden' );
            return;
        }

        var value_new = object.find( 'input' ).val( );
        var data_ajax = object.data( 'ajax' );
        var data_nonce = object.data( 'nonce' );

        $.ajax( {
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: "rwmb_get_terms",
                term: data_ajax.taxonomy,
                field: {
                    id: data_ajax.id,
                    query_args: data_ajax.query_args
                },
                ajax: data_ajax.ajax,
                _ajax_nonce: data_nonce
            },
            success: function( response ) {
                if( response.success === true ) {
                    var new_option = response.data.items.filter( ( x ) => x.label === value_new ).pop( );
                    object.closest( '.rwmb-input' ).find( 'select' ).append( '<option value="' + new_option.value + '">' + new_option.label + '</option>' )

                    if( object.closest( '.rwmb-input' ).find( 'select' ).val( ) === '' ) {
                        object.closest( '.rwmb-input' ).find( 'select' )
                            .attr( 'data-selected', new_option.value )
                            .val( new_option.value );
                    }

                    object.find( '.rwmb-taxonomy-add-form' ).addClass( 'rwmb-hidden' );
                    object.find( 'input' ).val( '' ).trigger( 'blur' );
                }
            }
        } );
    }    

    if ( rwmb.isGutenberg ) {
        var saved = true;
        wp.data.subscribe(function () {
            if (wp.data.select('core/editor').isSavingPost()) {
                saved = false;
            } else {
                if (!saved) {
                    saved = true;
                    $( '.rwmb-taxonomy-add' ).each( refreshAfterSave );
                }
            }
        });
    }

    rwmb.$document.on( 'blur', '.rwmb-taxonomy-add-form input', focusOutInput );
	rwmb.$document.on( 'click', '.rwmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
