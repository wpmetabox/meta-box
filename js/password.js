( function ( $, rwmb ) {
    'use strict';

    function toggle() {
        const $this = $( this ).parent().siblings( 'input' );
        $this.attr( 'type', $this.attr( 'type' ) === 'password' ? 'text' : 'password' );
        $( this ).attr( 'class', $this.attr( 'type' ) === 'password' ? 'password-icon show-icon' : 'password-icon hide-icon' );
    }

    rwmb.$document
        .on( 'click', '.password-icon', toggle );
} )( jQuery, rwmb );