( function ( $, rwmb ) {
    'use strict';

    function toggle() {
        const $this = $( this ).parent().siblings( 'input' );
        $this.attr( 'type', $this.attr( 'type' ) === 'password' ? 'text' : 'password' );
        $this.attr( 'type' ) === 'password' ?
            $( this ).removeClass( 'hide-icon' ).addClass( 'show-icon' ) :
            $( this ).removeClass( 'show-icon' ).addClass( 'hide-icon' );        
    }

    rwmb.$document
        .on( 'click', '.password-icon', toggle );
} )( jQuery, rwmb );