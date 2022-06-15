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

    var saved = true;
    wp.data.subscribe(function () {
        if (wp.data.select('core/editor').isSavingPost()) {
            saved = false;
        } else {
            if (!saved) {
                saved = true;
                setTimeout(() => {
                    window.location.reload(true);
                }, 2000);
            }

        }
    });

    rwmb.$document.on( 'blur', '.rwmb-taxonomy-add-form input', focusOutInput );
	rwmb.$document.on( 'click', '.rwmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
