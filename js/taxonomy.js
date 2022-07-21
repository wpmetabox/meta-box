( function ( $, rwmb ) {
	'use strict';

	function toggleAddInput( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'rwmb-hidden' );
	}

    function focusOutInput() {
        const required = $( this ).val() == '';
        $( this ).closest( '.rwmb-input' ).find( rwmb.inputSelectors ).removeClass( 'rwmb-error' ).rules( 'add', {
            required
        } );
    }

    rwmb.$document.on( 'blur', '.rwmb-taxonomy-add-form input', focusOutInput );
	rwmb.$document.on( 'click', '.rwmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
