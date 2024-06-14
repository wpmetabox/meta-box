( function ( $, rwmb ) {
	'use strict';

	function addNew() {
		$( this ).rwmbModal( {
			hideElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
            callback: function ( $modal, $modalContent ) {
                $modalContent.find( 'body' ).addClass( 'is-fullscreen-mode' );
                
                // Retry if the first statement fail
                setTimeout( () => {
                    $modalContent.find( 'body' ).addClass( 'is-fullscreen-mode' );
                }, 500 );
            },
			closeModalCallback: function ( $modal, $input ) {
                const objectId  = $modal.find( '#post_ID' ).val();
                const objectDisplay = !this.isBlockEditor ? $modal.find( '#title' ).val() : $modal.find( 'h1.editor-post-title' ).text();

                if ( !objectId ) {
                    return;
                }

                this.$objectId = objectId;
				this.$objectDisplay = objectDisplay;
			}
		} );
	}

	function init( e ) {
		const wrapper = e.target || e;

		$( wrapper ).find( '.rwmb-post-add-button' ).each( addNew );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', function ( e ) {
			init( $( e.target ).parent() );
		} );

} )( jQuery, rwmb );
