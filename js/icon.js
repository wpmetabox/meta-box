/**
 * Link: https://stackoverflow.com/questions/37386293/how-to-add-icon-in-select2
 */

( function ( $, rwmb ) {
    'use strict';

    function format_icon( event, options ) {
        $( this ).select2( {
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
    }

    rwmb.$document
        .on( 'format_icon', '.rwmb-icon', format_icon );
} )( jQuery, rwmb );