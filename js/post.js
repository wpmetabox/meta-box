( function ( $, rwmb ) {
	'use strict';

	function addNew() {
		const $this = $( this );

		$this.rwmbModal( {
			removeElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
			callback: function ( $modal, $modalContent ) {
				if ( !this.isBlockEditor ) {
					this.$objectId = $modalContent.find( '#post_ID' ).val();
					return;
				}

				setTimeout( () => {
					const $ui = $modalContent.find( '.interface-interface-skeleton' );
					$ui.css( {
						left: 0,
						top: 0
					} );
					$ui.find( '.interface-interface-skeleton__editor' ).css( 'overflow', 'scroll' );
				}, 500 );
			},
			closeModalCallback: function ( $modal, $input ) {
                this.$objectId = $modal.find( '#post_ID' ).val();
				this.$objectDisplay = !this.isBlockEditor ? $modal.find( '#title' ).val() : $modal.find( 'h1.editor-post-title' ).text();
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
