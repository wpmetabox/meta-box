( function ( $, rwmb ) {
    'use strict';

    function toggle() {
        const $this = $( this ).siblings( 'input' );
        $this.attr( 'type', $this.attr( 'type' ) === 'password' ? 'text' : 'password' );
        $( this ).html( $this.attr( 'type' ) === 'password' ? '<span class="show-icon"></span>' : '<span class="hide-icon"></span>' );
    }

    rwmb.$document
        .on( 'click', '.toggle-password', toggle );
} )( jQuery, rwmb );