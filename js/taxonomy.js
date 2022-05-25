( function ( $, rwmb ) {
	'use strict';
    var required = true;

	function toggleAddInput( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'rwmb-hidden' );
	}

    function focusOutInput( ) {
        if( $( this ).val( ) != '' ) {
            required = false;
        } else {
            required = true;
        }

        $( this ).parents( ".rwmb-input" ).find( rwmb.inputSelectors ).rules( 'add', {
            required: required
        } );
    }

    rwmb.$document.on( 'blur', '.rwmb-taxonomy-add-form input', focusOutInput );
	rwmb.$document.on( 'click', '.rwmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
